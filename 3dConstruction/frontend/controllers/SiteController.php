<?php
namespace frontend\controllers;

use common\models\Building;
use common\models\Config;
use common\models\Goods;
use common\models\Module;
use common\models\Order;
use common\models\OrderDetail;
use common\models\OrderForm;
use Yii;
use common\models\LoginForm;
use common\models\User;
use common\models\Model;
use common\models\Room;
use common\models\Floor;
use common\models\ManageSelfForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Request;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['login', 'signup', 'logout'],
                'rules' => [
                    [
                        'actions' => ['login', 'signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Manage self.
     *
     * @return mixed
     */
    public function actionManageSelf()
    {
        if (Yii::$app->user->can('selfManagement')) {
            $user = User::findIdentity(Yii::$app->getUser()->id);
            return $this->render('selfManagement', [
                'user' => $user,
            ]);
        }
    }

    /**
     * Display overview page.
     *
     * @return mixed
     */
    public function actionOverview()
    {
//        if (Yii::$app->user->can('viewFloor')) {
//            $floor = Config::getFloor()->floor;
//            $canEdit = Yii::$app->user->can('editRoom');
//
            return $this->render('overview');
//        }
    }

    /**
     * Display building page.
     *
     * @return mixed
     */
    public function actionViewBuilding()
    {
        if (Yii::$app->user->can('viewFloor')) {
            $building_id = isset(Yii::$app->request->get()['id']) ? Yii::$app->request->get()['id'] : 1;
            $building = Building::findById($building_id);

            return $this->render('viewBuilding', [
                'building' => $building,
            ]);
        }
    }

    /**
     * Display floor page.
     *
     * @return mixed
     */
    public function actionViewFloor()
    {
        if (Yii::$app->user->can('viewFloor')) {
            $floor_no = isset(Yii::$app->request->get()['floor']) ? Yii::$app->request->get()['floor'] : 1;
            $building = Building::findById(Yii::$app->request->get()['id']);
            $canEdit = Yii::$app->user->can('editRoom');

//            $floor = Floor::findById($floor_id);
//            $data = 'null';
//
//            if ($floor && $floor->data) {
//                $data = $floor->data;
//            } else {
//                Yii::$app->session->setFlash('error', 'no floor data');
//            }
            return $this->render('viewFloor', [
                'building' => $building,
                'floor_no' => $floor_no,
                'canEdit' => $canEdit,
//                'data' => $data,
            ]);
        }
    }

    public function actionGetFloorData() {
        if (Yii::$app->user->can('viewFloor')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $floor_no = $data['floor_no'];
                $building_id = $data['building_id'];
                $rooms = Room::findRoomsByFloor($building_id, $floor_no);
                $modules = Module::findAllModules();
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['rooms' => $rooms, 'modules' => $modules];
            }
        }
    }

    public function actionUpdateFloor() {
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();

            $rooms = Json::decode($data['data'], true)['room'];
            for ($i=0; $i<count($rooms); $i++) {
                $room = $rooms[$i];
                if ($room['id'] == 'undefined') {
                    $updateRoom = new Room();
                    $updateRoom->room_no = $room['room_no'];
                    $updateRoom->floor_no = $data['floor'];
                    $updateRoom->building_id = $data['id'];
                    $updateRoom->size = $room['size'];
                    $updateRoom->position = $room['position'];
                    $result = $updateRoom->save();
                } else {
                    $result = Room::updateLayout($room['id'], $room['room_no'], $room['position']);
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['result' => $result];
        }
    }

    /**
     * Display user's room page.
     *
     * @return mixed
     */
    public function actionViewRoom()
    {
        if (isset(Yii::$app->request->get()['room_id'])) {
            $room_id = Yii::$app->request->get()['room_id'];
        } else {
            $room = Room::findByUserId(Yii::$app->getUser()->id);
            if (isset($room)) {
                $room_id = $room->id;
            } else {
                Yii::$app->session->setFlash('error', 'no room');
                return $this->redirect(Yii::$app->request->getReferrer());
            }
        }

        if (Yii::$app->user->can('viewRoom') || Yii::$app->user->can('viewOwnRoom', ['room_id' => $room_id])) {
            $room = Room::findById($room_id);
            return $this->render('viewRoom', [
               'room' => $room,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'no authority');
            return $this->redirect(Yii::$app->request->getReferrer());
        }

    }

    /**
     * Display order list.
     *
     * @return mixed
     */
    public function actionViewOrder()
    {
        if (Yii::$app->user->can('viewOrder')) {
            $orders = Order::findByUserId(Yii::$app->getUser()->id);

            return $this->render('viewOrder', [
                'orders' => $orders,
            ]);
        }
    }

    /**
     * Display goods list.
     *
     * @return mixed
     */
    public function actionRoomService()
    {
        if (Yii::$app->user->can('roomService')) {
            $dataProvider = new ActiveDataProvider([
                'query' => Goods::find(),
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
            return $this->render('roomService', [
               'dataProvider' => $dataProvider
            ]);
        }
    }

    /**
     * Edit a room.
     *
     * @return mixed
     */
    public function actionEditRoom()
    {
        if (Yii::$app->user->can('editRoom')) {
            $room_no = Yii::$app->request->get()['room'];
            $floor_no = Yii::$app->request->get()['floor'];
            $building_id = Yii::$app->request->get()['building'];
            if ($room_no) {
                $room = Room::findRoomByNo($room_no, $building_id, $floor_no);
                return $this->render('editRoom', [
                    'room' => $room,
                ]);
            } else {
                Yii::$app->session->setFlash('error', 'no room');
                return $this->redirect(Yii::$app->request->getReferrer());
            }
        }

    }

    public function actionUpdateRoom()
    {
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $room_id = $data['id'];
            $room_data = $data['data'];
            $result = Room::updateRoom($room_id, $room_data);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['result' => $result];
        }
    }

    /**
     * Manage module.
     *
     * @return mixed
     */
    public function actionManageModule()
    {
        if (Yii::$app->user->can('modelManagement')) {
            $dataProvider = new ActiveDataProvider([
                'query' => Module::find(),
                'pagination' => [
                    'pageSize' => 5,
                ],
            ]);
            return $this->render('moduleManagement', [
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Manage model.
     *
     * @return mixed
     */
    public function actionManageModel()
    {
        if (Yii::$app->user->can('modelManagement')) {
            $dataProvider = new ActiveDataProvider([
                'query' => Model::find(),
                'pagination' => [
                    'pageSize' => 5,
                ],
            ]);
            return $this->render('modelManagement', [
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * 获取所有模型信息
     * @return array
     */
    public function actionFindAllModels()
    {
        if (Yii::$app->request->isAjax) {
            $models = Model::findAllModels();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['models' => $models];
        }
    }

    public function actionDeleteModel()
    {
        if (Yii::$app->user->can('modelManagement')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $id = $data['id'];
                $result = Model::deleteById($id);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result];
            }
        }

    }

    public function actionAddModel()
    {
        if (Yii::$app->user->can('modelManagement')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $model = new Model();
                $model->size = $data['size'];
                $model->scale = $data['scale'];
                $model->url2d = $data['url2d'];
                $model->url3d = $data['url3d'];
                $model->type = $data['type'];
                $result = $model->save() ? $model : null;
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result];
            }
        }

    }

    public function actionUpdateModel()
    {
        if (Yii::$app->user->can('modelManagement')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $result = Model::updateModel($data);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result];
            }
        }

    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
