<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Manage User';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-user">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>用户管理</p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'id',
            ],
            [
                'attribute' => 'username',
            ],
            [
                'attribute' => 'email',
            ],
            [
                'attribute' => 'status',
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'user_group',
            ],
        ],
    ]);
    ?>
</div>
