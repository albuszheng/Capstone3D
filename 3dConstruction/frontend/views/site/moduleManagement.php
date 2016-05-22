<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Manage Module';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-module">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>模板管理</p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'id',
            ],
            [
                'attribute' => 'name',
            ],
            [
                'attribute' => 'size',
            ],
//            [
//                'class' => 'yii\grid\ActionColumn',
//                'template' => '{update-model} {delete-model}'
//            ],
        ],
    ]);
    ?>

</div>
