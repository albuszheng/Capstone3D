<?php

/* @var $this yii\web\View */
/* @var $building \common\models\Building */

use yii\helpers\Html;
use frontend\assets\ThreeAsset;

ThreeAsset::register($this);

$this->title = 'View Building'.$building->building_no;
$this->params['breadcrumbs'][] = ['label' => 'Overview', 'url' => ['overview']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-view-building">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>建筑场景</p>

    <div id="canvas">
    </div>

</div>

<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/three.js"></script>
<script type="text/javascript" src="js/SceneLoad.js"></script>
<!--<script type="text/javascript" src="js/TrackballControls.js"></script>-->

<script type="text/javascript">
    var width = $('#canvas').width();
    var height = $('#canvas').height();
    var canvas = document.getElementById('canvas');

    // 加载场景
    function load() {
        var mouse = new THREE.Vector2(), INTERSECTED;
        var floors = [];

        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        camera.position.set( 5, 6, 15 );
        camera.lookAt( new THREE.Vector3(0, 3, 0) );

        var renderer = new THREE.WebGLRenderer();
        renderer.setClearColor(new THREE.Color(0xffffff, 1.0));
        renderer.setSize(width, height);

//        var controls = new THREE.TrackballControls(camera);
//        controls.rotateSpeed = 1.0;
//        controls.zoomSpeed = 1.2;
//        controls.panSpeed = 0.8;
//        controls.noZoom = false;
//        controls.noPan = false;
//        controls.staticMoving = true;
//        controls.dynamicDampingFactor = 0.3;

        var ambientLight = new THREE.AmbientLight( 0xffffff );
        scene.add( ambientLight );
        var directionalLight = new THREE.DirectionalLight( 0xffffff );
        directionalLight.position.set( 0, 0, 0 ).normalize();
        scene.add( directionalLight );

        var raycaster = new THREE.Raycaster();

        var texture = new THREE.TextureLoader().load( "img/floor.png" );

        for (var i = 1; i <= <?= $building->floor?>; i++) {
            var mesh = addMesh(i, new THREE.Vector3(0,i*0.8-0.8,0));
            floors.push(mesh);
        }
        addRoof(i, new THREE.Vector3(0,i*0.8-0.9,0));

        canvas.innerHTML="";
        canvas.appendChild(renderer.domElement);
        renderer.domElement.addEventListener( 'mousemove', onMouseMove, false );
        renderer.domElement.addEventListener( 'mousedown', onMouseDown, false );

        render();

        function render() {
//            controls.update();
            requestAnimationFrame(render);
            renderer.render(scene, camera);
        }

        function onMouseMove( event ) {
            event.preventDefault();
            mouse.x = ( (event.pageX - event.currentTarget.offsetLeft) / width ) * 2 - 1;
            mouse.y = - ( (event.pageY - event.currentTarget.offsetTop) / height ) * 2 + 1;

            raycaster.setFromCamera( mouse, camera );
            var intersects = raycaster.intersectObjects( floors );
            if ( intersects.length > 0 ) {
                if ( INTERSECTED !== intersects[ 0 ].object ) {
                    if ( INTERSECTED ) INTERSECTED.material.emissive.setHex( INTERSECTED.currentHex );
                    INTERSECTED = intersects[ 0 ].object;
                    INTERSECTED.currentHex = INTERSECTED.material.emissive.getHex();
                    INTERSECTED.material.emissive.setHex( 0x00ff00 );
                }
            } else {
                if ( INTERSECTED ) INTERSECTED.material.emissive.setHex( INTERSECTED.currentHex );
                INTERSECTED = null;
            }

            render();
        }

        function onMouseDown(event) {
            event.preventDefault();
            if (INTERSECTED !== null) {
                window.location.href = 'index.php?r=site/view-floor&floor='+INTERSECTED.floorid+'&id='+<?= $building->id?>;
            }
        }

        function addMesh(id, position) {
            var position = position || new THREE.Vector3();
            var geometry = new THREE.BoxGeometry(5,0.8,5);
            var material = new THREE.MeshLambertMaterial( { map: texture } );
            var mesh = new THREE.Mesh(geometry, material);
            mesh.floorid = id;
//            mesh.material.map.wrapS = THREE.RepeatWrapping;
//            mesh.material.map.wrapT = THREE.RepeatWrapping;
//            mesh.material.map.repeat.set(5, 1);
            mesh.position.copy(position);
            scene.add(mesh);
            return mesh;
        }

        function addRoof(id, position) {
            var position = position || new THREE.Vector3();
            var geometry = new THREE.BoxGeometry(5,0.6,5);
            var texture = new THREE.TextureLoader().load( "img/nav.png" );
            var matArray = [];
            var mapMaterial = new THREE.MeshBasicMaterial({map:texture});
//            mapMaterial.map.wrapS = THREE.RepeatWrapping;
//            mapMaterial.map.wrapT = THREE.RepeatWrapping;
//            mapMaterial.map.repeat.set(10,1);
            var roofMaterial = new THREE.MeshBasicMaterial({map:new THREE.TextureLoader().load( "img/roof2.png" )});
            matArray.push(mapMaterial);
            matArray.push(mapMaterial);
            matArray.push(roofMaterial);
            matArray.push(mapMaterial);
            matArray.push(mapMaterial);
            matArray.push(new THREE.MeshBasicMaterial({}));
            var material = new THREE.MeshFaceMaterial(matArray);
//            var material = new THREE.MeshLambertMaterial( { color: 0xfeb74c, map: texture } );
            var mesh = new THREE.Mesh(geometry, material);
            mesh.position.copy(position);
            scene.add(mesh);
        }
    }

    window.onload = load;
</script>