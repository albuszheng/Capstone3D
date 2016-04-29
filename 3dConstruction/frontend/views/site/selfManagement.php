<?php

/* @var $this yii\web\View */
/* @var $user \common\models\User */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Manage self';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-self">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>个人管理</p>

    <div id="info">
        <?php echo DetailView::widget([
            'model' => $user,
            'attributes' => [
                'id',
                'username',
                'email',
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]); ?>
    </div>

</div>
