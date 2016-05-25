<?php

/* @var $this yii\web\View */
/* @var $modules \yii\db\ActiveRecord[] */

use yii\helpers\Html;

$this->title = 'Manage Module';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-module">

    <div class="modal fade template-canvas" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-title"></div>
            <div class="modal-content canvas" id="module-canvas">

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
                        <td><button class="btn btn-link template-name" id=<?= $module->id?> data-toggle="modal" data-target=".template-canvas" data-content="<?= $module->data?>" data-name="<?= $module->name?>"><?= $module->name?></button></td>
                        <td><?= $module->size?></td>
                    </tr>
                <?php endforeach;?>

                </tbody>
            </table>
        </div>
    </div>

</div>

<script type="text/javascript" src="js/pixi.js"></script>
<script type="text/javascript" src="js/three.js"></script>
<script type="text/javascript" src="js/ThreeBSP.js"></script>
<script type="text/javascript" src="js/FirstPersonControls.js"></script>
<script type="text/javascript" src="js/ColladaLoader.js"></script>
<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/SceneExport.js"></script>
<script type="text/javascript" src="js/SceneLoad.js"></script>

<script>
    $(".template-name").on('show.bs.modal', function (event) {


        var pos = document.getElementById("pos");
        var rot = document.getElementById("rot");
        var models = [];

        $.ajax({
            type:'post',
            data:{},
            url:'<?php echo Yii::$app->getUrlManager()->createUrl('/site/find-all-models') ?>',
            async : false,
            success:function(data) {
                models = data.models;
            },

            error:function(xhr) {
                console.log(xhr.responseText);
            }

        });

        var stage, floor, walls, group;

        var button = $(event.relatedTarget);
        var data = button.data('content');

        var width2d = $('#module-canvas').width();
        var height2d = $('#module-canvas').height();

        var loader = new SceneLoad();
        stage = loader.load2d(data, width2d, height2d, document.getElementById('#module-canvas'), models);
        updateInfo();

        var modal = $(this);
        modal.find('.modal-title').text(button.data('name'));
    })
</script>
