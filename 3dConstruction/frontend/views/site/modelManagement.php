<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Manage Model';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-model">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>模型管理</p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'columns' => [
//            [
//                'attribute' => 'id',
//            ],
//            [
//                'attribute' => 'size',
//            ],
//            [
//                'attribute' => 'scale',
//            ],
//            [
//                'attribute' => 'url2d',
//            ],
//            [
//                'attribute' => 'url3d',
//            ],
//            [
//                'attribute' => 'type',
//            ],
//            [
//                'class' => 'yii\grid\ActionColumn',
//                'template' => '{update-model} {delete-model}'
//            ],
//        ],
    ]);
    ?>

</div>
