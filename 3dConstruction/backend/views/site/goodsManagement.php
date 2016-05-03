<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Manage Goods';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-goods">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>商品管理</p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'text',
            ],
            [
                'attribute' => 'price',
                'format' => ['decimal', '2'],
            ]
        ],
    ]);
    ?>

</div>
