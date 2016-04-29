<?php

/* @var $this yii\web\View */
/* @var $floor_id integer */
/* @var $data string */

use yii\helpers\Html;
use frontend\assets\ThreeAsset;

ThreeAsset::register($this);

$this->title = 'View Floor'.$floor_id;
$this->params['breadcrumbs'][] = ['label' => 'Overview', 'url' => ['overview']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-view-floor">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>楼层场景</p>

    <div id="canvas">
    </div>

</div>

<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/three.js"></script>
<script type="text/javascript" src="js/SceneLoad.js"></script>

<script type="text/javascript">
    var data = null;

    var width = $('#canvas').width();
    var height = $('#canvas').height();
    var canvas = document.getElementById('canvas');

    // 加载场景
    function load() {
        canvas.innerHTML = <?php echo $floor_id?>;

        if (<?php echo $data ?> !== null) {
            data = <?php echo $data ?>;
        }


        if (data !== null) {
            canvas.innerHTML = data;
        }
    }

    window.onload = load;
</script>