<?php

/* @var $this yii\web\View */
/* @var $room \common\models\Room */

use yii\helpers\Html;
use frontend\assets\ThreeAsset;

ThreeAsset::register($this);

$this->title = 'View Room'.$room->room_no;
//$this->params['breadcrumbs'][] = ['label' => 'Overview', 'url' => ['overview']];
$this->params['breadcrumbs'][] = ['label' => 'View Building', 'url' => ['view-building', 'id'=>$room->building_id]];
$this->params['breadcrumbs'][] = ['label' => 'View Floor'.$room->floor_no, 'url' => ['view-floor', 'floor'=>$room->floor_no, 'id'=>$room->building_id]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-view-room">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>This is your room:</p>
    <button onclick="view2d()">2d</button>
    <button onclick="view3d()">3d</button>

    <div id="canvas2d">
    </div>
    <div id="canvas3d">
    </div>

</div>

<script type="text/javascript" src="js/pixi.js"></script>
<script type="text/javascript" src="js/three.js"></script>
<script type="text/javascript" src="js/ThreeBSP.js"></script>
<script type="text/javascript" src="js/FirstPersonControls.js"></script>
<script type="text/javascript" src="js/TrackballControls.js"></script>
<script type="text/javascript" src="js/ColladaLoader.js"></script>
<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/SceneExport.js"></script>
<script type="text/javascript" src="js/SceneLoad.js"></script>

<script type="text/javascript">
    var models = [];
    var viewMode = 1; //0:2d  1:3d
    var data = null;

    var width2d = $('#canvas2d').width();
    var width3d = $('#canvas3d').width();
    var height2d = $('#canvas2d').height();
    var height3d = $('#canvas3d').height();
    var canvas2d = document.getElementById('canvas2d');
    var canvas3d = document.getElementById('canvas3d');

    // 加载场景
    function load() {
        if (<?php echo $room->data ?> !== null) {
            data = <?php echo $room->data ?>;
        }
        console.log(data);


        if (data !== null) {
            if (data.type === "scene") {
                // 获取所有模型信息
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

                view2d();
                console.log('load');
            }
        }
    }

    // 查看2d场景
    function view2d() {
        if (data !== null) {
            if (viewMode === 0) {
                return;
            }

            var loader = new SceneLoad();
            loader.load2d(data, width2d, height2d, canvas2d, models);

            viewMode = 0;
            $('#canvas3d').css('display', 'none');
            $('#canvas2d').css('display', 'block');
        }
    }

    // 查看3d场景
    function view3d() {
        if (data !== null) {
            if (viewMode === 1) {
                return;
            }

            var loader = new SceneLoad();
            loader.load3d(data, width3d, height3d, canvas3d, models);
            viewMode = 1;
            $('#canvas2d').css('display', 'none');
            $('#canvas3d').css('display', 'block');
        }
    }


    window.onload = load;
</script>