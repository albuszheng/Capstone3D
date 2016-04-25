<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use common\models\LoginForm;
use yii\filters\VerbFilter;
use common\models\Room;

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
                'only' => ['login', 'logout', 'error', 'index'],
                'rules' => [
                    [
                        'actions' => ['index', 'login', 'error'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'manage-self', 'view-room', 'view-floor', 'logout', 'error'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['manage-user', 'manage-authority'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                    [
                        'actions' => ['register-user', 'manage-goods'],
                        'allow' => true,
                        'roles' => ['staff'],
                    ],
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
        return $this->render('selfManagement');
    }

    /**
     * Display floor page.
     *
     * @return mixed
     */
    public function actionViewFloor()
    {
        return $this->render('viewFloor');
    }

    /**
     * Display user's room page.
     *
     * @return mixed
     */
    public function actionViewRoom()
    {
        $room = Room::findById('1');
        $data = "null";

        if ($room && $room->data) {
            $data = $room->data;
        } else {
            Yii::$app->session->setFlash('error', 'no room');
        }

        return $this->render('viewRoom', [
            'data' => $data,
        ]);
    }

    /**
     * Manage User.
     *
     * @return mixed
     */
    public function actionManageUser()
    {
        return $this->render('userManagement');
    }

    /**
     * Manage Authority.
     *
     * @return mixed
     */
    public function actionManageAuthority()
    {
        return $this->render('authManagement');
    }

    /**
     * Register a user.
     *
     * @return mixed
     */
    public function actionRegisterUser()
    {
        return $this->render('registerUser');
    }

    /**
     * Manage goods.
     *
     * @return mixed
     */
    public function actionManageGoods()
    {
        return $this->render('goodsManagement');
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
