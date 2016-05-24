<?php

/* @var $this yii\web\View */
/* @var $users \yii\db\ActiveRecord[] */
/* @var $operations \yii\db\ActiveRecord[] */
/* @var $authorities \yii\db\ActiveRecord[] */

use yii\helpers\Html;
use common\models\User;

$this->title = 'Manage Authority';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-authority">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="container">
        <h2 class="title">权限管理</h2>
        <div class="template-list">
            <table class="table table-hover">
                <thead>
                <tr>
                    <td>#</td>
                    <td>用户名</td>
                    <td>邮箱</td>
                    <td>创建时间</td>
                    <td>更新时间</td>
                    <td>用户组</td>
                    <td>操作</td>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user->id?></td>
                        <td><?= $user->username?></td>
                        <td><?= $user->email?></td>
                        <td><?= date('Y-m-d H:i:s', $user->created_at) ?></td>
                        <td><?= date('Y-m-d H:i:s', $user->updated_at)?></td>
                        <td><?php
                            switch ($user->user_group) {
                                case User::GROUP_ADMIN:
                                    echo '管理员';
                                    break;
                                case User::GROUP_STAFF:
                                    echo '前台人员';
                                    break;
                                case User::GROUP_ENGINEER:
                                    echo '工程人员';
                                    break;
                                case User::GROUP_USER:
                                    echo '普通住户';
                                    break;
                            }

                            ?></td>
                        <td>
                        <?php
                        foreach ($operations as $operation): ?>
                         <button class="btn btn-link template-name" id=<?= $operation->id?> data-toggle="modal" data-target=".template-canvas"><?= $operation->operation?></button>
                        <?php endforeach;?>
                        </td>
                    </tr>
                <?php endforeach;?>

                </tbody>
            </table>
        </div>

        <div class="template-list">
            <table class="table table-hover">
                <thead>
                <tr>
                    <td>用户组</td>
                    <td>权限</td>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($authorities as $authority): ?>
                    <tr>
                        <td><?php
                            switch ($authority['user_id']) {
                                case User::GROUP_ADMIN:
                                    echo '管理员';
                                    break;
                                case User::GROUP_STAFF:
                                    echo '前台人员';
                                    break;
                                case User::GROUP_ENGINEER:
                                    echo '工程人员';
                                    break;
                                case User::GROUP_USER:
                                    echo '普通住户';
                                    break;
                            }

                            ?></td>
                        <td><?= $authority['description'] ?></td>
                    </tr>
                <?php endforeach;?>

                </tbody>
            </table>
        </div>
    </div>
</div>
