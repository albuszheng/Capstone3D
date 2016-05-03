<?php

/* @var $this yii\web\View */
/* @var $orders array */

use yii\helpers\Html;

$this->title = 'View Order';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-view-order">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>订单列表</p>

    <ul>
        <?php foreach ($orders as $order): ?>
            <li>
                <?= Html::encode("{$order->time}") ?>
            </li>
        <?php endforeach; ?>
    </ul>

</div>
