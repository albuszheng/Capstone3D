<?php

/* @var $this yii\web\View */
/* @var $data string */

use yii\helpers\Html;
use frontend\assets\ThreeAsset;

ThreeAsset::register($this);

$this->title = 'View Room';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-view-room">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>This is your room:</p>
    <button onclick="view2d()">2d</button>
    <button onclick="view3d()">3d</button>

    <div id="canvas">
    </div>

</div>


<script type="text/javascript" src="js/three.js"></script>
<script type="text/javascript" src="js/ThreeBSP.js"></script>
<script type="text/javascript" src="js/FirstPersonControls.js"></script>
<script type="text/javascript" src="js/ColladaLoader.js"></script>
<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/SceneExport.js"></script>
<script type="text/javascript" src="js/SceneLoad.js"></script>

<script type="text/javascript">
    var models = [];
    var viewMode = 1; //0:2d  1:3d
    var data = null;

    var width = $('#canvas').width();
    var height = $('#canvas').height();
    var canvas = document.getElementById('canvas');

    // 加载场景
    function load() {
        if (<?php echo $data ?> !== null) {
            data = <?php echo $data ?>;
        }


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
        if (viewMode === 0) {
            return;
        }

        var loader = new SceneLoad();
        loader.load2d(data, width, height, canvas, models);

        viewMode = 0;
    }

    // 查看3d场景
    function view3d() {
        if (viewMode === 1) {
            return;
        }

        var loader = new SceneLoad();
        loader.load3d(data, width, height, canvas, models);
        viewMode = 1;
    }


    window.onload = load;
</script>