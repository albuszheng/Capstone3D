<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>

    <?php $this->head() ?>
<!--    <link href="/frontend/web/css/screen.css" rel="stylesheet" type="text/css">-->
</head>
<body class="user">
<?php $this->beginBody() ?>

<!--<div class="wrap">-->
    <?php
    NavBar::begin([
        'brandLabel' => 'House Hotel',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => '主页', 'url' => ['/site/index']],
//        ['label' => 'About', 'url' => ['/site/about']],
//        ['label' => 'Contact', 'url' => ['/site/contact']],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => '注册', 'url' => ['/site/signup']];
        $menuItems[] = ['label' => '登录', 'url' => ['/site/login']];
    } elseif (Yii::$app->user->can('admin')) {
//        Yii::$app->session->setFlash('success', Url::base(true));
        Yii::$app->getResponse()->redirect('http://localhost/backend/web/index.php', 301);
    } elseif (Yii::$app->user->can('staff')) {
        Yii::$app->getResponse()->redirect('http://localhost/backend/web/index.php', 301);
    }
    else {
        $menuItems[] = ['label' => '个人管理', 'url' => ['/site/manage-self']];
        $menuItems[] = ['label' => 'test', 'url' => ['/site/overview']];
        $menuItems[] = ['label' => '查看场景', 'url' => ['/site/view-building']];

        if (Yii::$app->user->can('user')) {
            $menuItems[] = ['label' => '我的房间', 'url' => ['/site/view-room']];
            $menuItems[] = ['label' => '订单列表', 'url' => ['/site/view-order']];
            $menuItems[] = ['label' => '客房服务', 'url' => ['/site/room-service']];
        } else if (Yii::$app->user->can('engineer')) {
            $menuItems[] = ['label' => '模板管理', 'url' => ['/site/manage-module']];
        }


        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                '注销 (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link']
            )
            . Html::endForm()
            . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
<!--</div>-->

<!--<footer class="footer">-->
<!--    <div class="container">-->
<!--        <p class="pull-left">&copy; House Hotel --><?//=date('Y')?><!-- </p>-->
<!---->
<!--        <p class="pull-right"> --><?//=Yii::powered()?><!--</p>-->
<!--    </div>-->
<!--</footer>-->


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
