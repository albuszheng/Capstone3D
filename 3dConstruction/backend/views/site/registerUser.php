<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Register User';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-register-user">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>住户登记</p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'room_id',
            ],
            [
                'attribute' => 'user_id',
            ],
            [
                'attribute' => 'last_modify_id',
            ],
            [
                'attribute' => 'last_modify_time',
            ]
        ],
    ]);
    ?>
</div>
