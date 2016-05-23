<?php

/* @var $this yii\web\View */
/* @var $canEdit boolean */

use yii\helpers\Html;
use frontend\assets\ThreeAsset;

ThreeAsset::register($this);

$this->title = 'Overview';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-overview">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>总览场景</p>


    <div id="canvas3d" style="display: block">
    </div>

</div>

<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/three.js"></script>

<script type="text/javascript">
    var width = $('#canvas3d').width();
    var height = $('#canvas3d').height();
    var canvas = document.getElementById('canvas3d');

    // 加载场景
    function load() {
        $.ajax({
            type:'post',
            data:{},
            url:'<?php echo Yii::$app->getUrlManager()->createUrl('/site/get-buildings') ?>',
            success:function(data) {
                var loader = new SceneLoad();
                loader.loadOverview(data.buildings, width, height, canvas, <?php
                    if($canEdit) {
                        echo 1;
                    } else {
                        echo 0;
                    } ?>);
            },

            error:function(xhr) {
                console.log(xhr.responseText);
            }

        });

    }

    window.onload = load;
</script>