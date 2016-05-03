<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Room Service';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-room-service">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>客房服务</p>
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
