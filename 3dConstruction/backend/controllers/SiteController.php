<?php
namespace backend\controllers;

use common\models\Config;
use common\models\Operation;
use common\models\Sensor;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use common\models\LoginForm;
use yii\filters\VerbFilter;
use common\models\AuthorityLog;
use common\models\User;
use common\models\Room;
use common\models\Model;
use common\models\Floor;;
use common\models\Goods;
use common\models\Building;
use common\models\Module;

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
                'only' => ['login', 'logout'],
                'rules' => [
                    [
                        'actions' => ['login'],
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
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

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
        if (Yii::$app->user->can('viewFloor')) {
            $canEdit = Yii::$app->user->can('editRoom');
            return $this->render('overview', [
                'canEdit' => $canEdit,
            ]);
        }
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

    public function actionGetBuildings() {
        if (Yii::$app->request->isAjax) {
            $buildings = Building::findAllBuildings();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['buildings' => $buildings];
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

            return $this->render('viewFloor', [
                'building' => $building,
                'floor_no' => $floor_no,
                'canEdit' => $canEdit,
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
            $sensors = Sensor::findSensorsByRoom($room_id);
            return $this->render('viewRoom', [
                'room' => $room,
                'sensors' => $sensors,
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'no authority');
            return $this->redirect(Yii::$app->request->getReferrer());
        }

    }

    /**
     * Manage User.
     *
     * @return mixed
     */
    public function actionManageUser()
    {
        if (Yii::$app->user->can('userManagement')) {
            $dataProvider = new ActiveDataProvider([
                'query' => User::find(),
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
            return $this->render('userManagement', [
                'dataProvider' => $dataProvider
            ]);
        }
    }

    public function actionDeleteUser() {
        if (Yii::$app->user->can('userManagement')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $id = $data['id'];
                $result = Model::deleteById($id);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result];
            }
        }
    }

    /**
     * Manage Authority.
     *
     * @return mixed
     */
    public function actionManageAuthority()
    {
        if (Yii::$app->user->can('authManagement')) {
            $users = User::findAll(['status' => User::STATUS_ACTIVE]);
            $operations = Operation::findAllOperations();
            $sql = 'select user_id,description from auth_item_child ic,auth_assignment a,auth_item i where ic.parent=a.item_name and ic.child=i.name;';
            $authorities = Yii::$app->db->createCommand($sql)->query();
            return $this->render('authManagement', [
                'users' => $users,
                'operations' => $operations,
                'authorities' => $authorities,
            ]);
        }
    }

    public function actionUpdateAuthority()
    {
        if (Yii::$app->user->can('authManagement')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $log = new AuthorityLog();
                $log->operator_id = Yii::$app->getUser()->id;
                $log->user_id = $data['user_id'];
                $log->operation_id = $data['operation_id'];
                $log->time = date('Y-m-d H:i:s');
                $result = ($log->save()) && (User::updateUserGroup($data['user_id'], $data['user_group']));
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result];
            }
        }
    }

    /**
     * Register a user.
     *
     * @return mixed
     */
    public function actionRegisterUser()
    {
        if (Yii::$app->user->can('registerUser')) {
            $dataProvider = new ActiveDataProvider([
                'query' => Room::find(),
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
            return $this->render('registerUser', [
                'dataProvider' => $dataProvider
            ]);
        }
    }

    public function actionRegisterRoom()
    {
        if (Yii::$app->user->can('registerUser')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $room_id = $data['id'];
                $result = false;
                if (Room::isRegisteredRoom($room_id)) {
                    $message = 'This room has been registered!';
                } else {
                    $result = Room::registerRoom($room_id, $data['user_id']);
                    $message = 'Register success!';
                }
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result, 'message' => $message];
            }
        }
    }

    public function actionUnregisterRoom()
    {
        if (Yii::$app->user->can('registerUser')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $result = Room::unregisterRoom($data['id']);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result];
            }
        }
    }

    /**
     * Manage goods.
     *
     * @return mixed
     */
    public function actionManageGoods()
    {
        if (Yii::$app->user->can('goodsManagement')) {
            $dataProvider = new ActiveDataProvider([
                'query' => Goods::find(),
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
            return $this->render('goodsManagement', [
                'dataProvider' => $dataProvider
            ]);
        }
    }

    public function actionAddGoods() {
        if (Yii::$app->user->can('goodsManagement')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $goods = new Goods();
                $goods->name = $data['name'];
                $goods->price = $data['price'];
                $result = $goods->save() ? $goods : null;
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result];
            }
        }
    }

    public function actionUpdateGoods() {
        if (Yii::$app->user->can('goodsManagement')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $result = Goods::updateGoods($data['id'], $data['name'], $data['price']);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result];
            }
        }
    }

    public function actionDeleteGoods() {
        if (Yii::$app->user->can('goodsManagement')) {
            if (Yii::$app->request->isAjax) {
                $data = Yii::$app->request->post();
                $result = Goods::deleteGoods($data['id']);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['result' => $result];
            }
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * 获取所有模型信息
     * @return array
     */
    public function actionFindAllModels()
    {
        if (Yii::$app->request->isAjax) {
            $sql = 'select * from model';
            $models = Model::findBySql($sql)->all();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['models' => $models];
        }
    }
}
