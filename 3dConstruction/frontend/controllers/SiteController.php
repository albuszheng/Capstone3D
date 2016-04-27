<?php
namespace frontend\controllers;

use common\models\Model;
use Yii;
use common\models\LoginForm;
use common\models\Room;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
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
            return $this->render('selfManagement');
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
            return $this->render('viewFloor');
        }
    }

    /**
     * Display user's room page.
     *
     * @return mixed
     */
    public function actionViewRoom()
    {
        if (Yii::$app->user->can('viewRoom')) {
            $room = Room::findByUserId(Yii::$app->getUser()->id);
            $data = "null";

            if ($room) {
                if ($room->data) {
                    $data = $room->data;
                }
            } else {
                Yii::$app->session->setFlash('error', 'no room');
            }

            return $this->render('viewRoom', [
                'data' => $data,
            ]);
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
            return $this->render('viewOrder');
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
            return $this->render('roomService');
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
            $room = Room::findById('1');
            $data = 'null';
            $id = 'null';

            if ($room) {
                $id = $room->id;
                if ($room->data) {
                    $data = $room->data;
                }
            } else {
                Yii::$app->session->setFlash('error', 'no room');
            }

            return $this->render('editRoom', [
                'data' => $data,
                'room_id' => $id,
            ]); $this->render('roomService');
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
            return $this->render('modelManagement');
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

    /**
     * 根据id获取模型信息
     * @return null|static
     */
    public function actionModel()
    {
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $model = Model::findById($data['id']);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['model' => $model];
        }
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

    public function actionSaveModel()
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
}
