<?php

/* @var $this yii\web\View */
/* @var $modules \yii\db\ActiveRecord[] */

use yii\helpers\Html;

$this->title = 'Manage Module';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-module">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>模板管理</p>

    <div class="modal fade template-canvas" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content canvas">
                //put your canvas in here
            </div>
        </div>
    </div>

    <div class="container">
        <h2 class="title">模版管理</h2>
        <div class="template-list">
            <table class="table table-hover">
                <thead>
                <tr>
                    <td>#</td>
                    <td>模版名称</td>
                    <td>尺寸</td>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($modules as $module): ?>
                    <tr>
                        <td><?= $module->id?></td>
                        <td><button class="btn btn-link template-name" id=<?= $module->id?> data-toggle="modal" data-target=".template-canvas"><?= $module->name?></button></td>
                        <td><?= $module->size?></td>
<!--                        <td>--><?//= $module->data?><!--</td>-->
                    </tr>
                <?php endforeach;?>

                </tbody>
            </table>
        </div>
    </div>

</div>
