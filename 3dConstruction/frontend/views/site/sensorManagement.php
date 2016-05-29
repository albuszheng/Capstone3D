<?php

/* @var $this yii\web\View */
/* @var $sensors \common\models\Sensor[] */

use yii\helpers\Html;

$this->title = 'Manage Sensor';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-sensor">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="container">
        <h2 class="title">传感器管理</h2>
        <div class="template-list">
            <table class="table table-hover">
                <thead>
                <tr>
                    <td>#</td>
                    <td>名称</td>
                    <td>参数</td>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($sensors as $sensor): ?>
                    <tr>
                        <td><?= $sensor->id?></td>
                        <td><?= $sensor->name?></td>
                        <?php
                        $params = explode(';', $sensor->param);
                        foreach ($params as $param):
                        ?>
                        <td><?= $param?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach;?>

                </tbody>
            </table>
        </div>
    </div>


</div>
