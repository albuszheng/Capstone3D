<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use common\models\LoginForm;
use yii\filters\VerbFilter;
use common\models\Room;
use common\models\Model;

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
            $room = Room::findById('1');
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
     * Manage User.
     *
     * @return mixed
     */
    public function actionManageUser()
    {
        if (Yii::$app->user->can('userManagement')) {
            return $this->render('userManagement');
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
            return $this->render('authManagement');
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
            return $this->render('registerUser');
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
            return $this->render('goodsManagement');
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
