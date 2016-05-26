<?php

/* @var $this yii\web\View */
/* @var $building \common\models\Building */
/* @var $floor_no integer */
/* @var $canEdit boolean */

use yii\helpers\Html;
use frontend\assets\ThreeAsset;

ThreeAsset::register($this);

$this->title = 'View Floor'.$floor_no;
$this->params['breadcrumbs'][] = ['label' => 'Overview', 'url' => ['overview']];
$this->params['breadcrumbs'][] = ['label' => 'View Building'.$building->building_no, 'url' => ['view-building', 'id'=>$building->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-view-floor">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>楼层场景</p>

    <div id="button-group" class="btn-group"></div>

    <div id="canvas">
    </div>

</div>

<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/pixi.js"></script>
<script type="text/javascript" src="js/SceneLoad.js"></script>

<script type="text/javascript">
    var width = $('#canvas').width();
    var height = $('#canvas').height();
    var canvas = document.getElementById('canvas');
    var btnGroup = document.getElementById('button-group');

    // 加载场景
    function load() {
        $.ajax({
            type:'post',
            data:{floor_no:<?= $floor_no ?>, building_id:<?= $building->id ?>},
            url:'<?php echo Yii::$app->getUrlManager()->createUrl('/site/get-floor-data') ?>',
            success:function(data) {
                var step = Math.min(width/<?= $building->width ?>, height/<?= $building->height ?>);
                var loader = new SceneLoad();
                loader.loadfloor(data, step, <?= $building->width ?>, <?= $building->height ?>, canvas, btnGroup,<?php
                    if($canEdit) {
                        echo 1;
                    } else {
                        echo 0;
                    } ?>, <?= $building->id ?>, <?= $floor_no ?>);
            },

            error:function(xhr) {
                console.log(xhr.responseText);
            }

        });
    }

    window.onload = load;
</script>