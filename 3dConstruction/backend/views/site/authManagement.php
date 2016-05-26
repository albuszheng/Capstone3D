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
    <div class="modal fade template-canvas" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">

        <div class="modal-dialog modal-lg" style="background-color: #5cb85c">
            <div class="modal-body">
            </div>
            <div class="modal-footer" id="operate-btn">
<!--                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>-->
<!--                <button type="button" class="btn btn-primary">确定</button>-->
            </div>

        </div>
    </div>

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
                         <button class="btn btn-link template-name" id=<?= $operation->id?> data-toggle="modal" data-target=".template-canvas" data-operate="<?= $operation->operation ?>" data-user="<?= $user->id ?>"><?= $operation->operation?></button>
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

<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/bootstrap.js"></script>
<script>
    $(".template-canvas").on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var operation = button.data('operate');
        var user = button.data('user');
        var operation_id = button.context.id;
        var modal = $(this);
        modal.find('.modal-body').text("Sure to change user "+user+" "+operation+"?");

        var operate_btn = document.getElementById('operate-btn');
        operate_btn.innerHTML="";
        var cancel_btn = createModelButton(operate_btn, '取消');
        cancel_btn.setAttribute('type', 'button');
        cancel_btn.setAttribute('class', 'btn btn-default');
        cancel_btn.setAttribute('data-dismiss', 'modal');
        var confirm_btn = createModelButton(operate_btn, '确定');
        confirm_btn.setAttribute('type', 'button');
        confirm_btn.setAttribute('class', 'btn btn-primary');
        confirm_btn.setAttribute('data-dismiss', 'modal');
        confirm_btn.addEventListener('click', function() {changeAuthority();}, false);

        function changeAuthority() {
            var user_group = <?= User::GROUP_USER?>;
            switch (operation) {
                case 'to admin':
                    user_group = <?= User::GROUP_ADMIN?>; break;
                case 'to engineer':
                    user_group = <?= User::GROUP_ENGINEER?>; break;
                case 'to staff':
                    user_group = <?= User::GROUP_STAFF?>; break;
                default:
                    break;
            }

            $.ajax({
                type:'post',
                data:{user_id: user, operation_id: operation_id, user_group: user_group},
                url:'index.php?r=site/update-authority',
                success: function (data) {
                    location.reload();
                    alert(data.result);
                },

                error:function(xhr) {
                    console.log(xhr.responseText);
                }

            });
        }

        function createModelButton(element, name) {
            var model = document.createElement('button');
            var modelText = document.createTextNode(name);
            model.appendChild(modelText);
            element.appendChild(model);
            return model;
        }
    })
</script>
