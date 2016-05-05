<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use frontend\assets\ThreeAsset;

ThreeAsset::register($this);

$this->title = 'Overview';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-overview">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>总览场景</p>

    <div id="canvas">
    </div>

</div>

<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/three.js"></script>
<script type="text/javascript" src="js/SceneLoad.js"></script>

<script type="text/javascript">
    var width = $('#canvas').width();
    var height = $('#canvas').height();
    var canvas = document.getElementById('canvas');

    // 加载场景
    function load() {
        var mouse = new THREE.Vector2(), INTERSECTED;

        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        camera.position.set( 5, 6, 15 );
        camera.lookAt( new THREE.Vector3(0, 3, 0) );

        var renderer = new THREE.WebGLRenderer();
        renderer.setClearColor(new THREE.Color(0xffffff, 1.0));
        renderer.setSize(width, height);

        var ambientLight = new THREE.AmbientLight( 0xffffff );
        scene.add( ambientLight );
        var directionalLight = new THREE.DirectionalLight( 0xffffff );
        directionalLight.position.set( 0, 0, 0 ).normalize();
        scene.add( directionalLight );

        var raycaster = new THREE.Raycaster();

        var texture = new THREE.TextureLoader().load( "model/images/wall/2.jpg" );

        for (var i = 1; i < 10; i++) {
            addMesh(i, new THREE.Vector3(0,i*0.8-0.8,0));
        }

        canvas.innerHTML="";
        canvas.appendChild(renderer.domElement);
        renderer.domElement.addEventListener( 'mousemove', onMouseMove, false );
        renderer.domElement.addEventListener( 'mousedown', onMouseDown, false );

        render();

        function render() {
            requestAnimationFrame(render);

            renderer.render(scene, camera);
        }

        function onMouseMove( event ) {
            event.preventDefault();
            mouse.x = ( (event.pageX - event.currentTarget.offsetLeft) / width ) * 2 - 1;
            mouse.y = - ( (event.pageY - event.currentTarget.offsetTop) / height ) * 2 + 1;

            raycaster.setFromCamera( mouse, camera );
            var intersects = raycaster.intersectObjects( scene.children );
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
                window.location.href = 'index.php?r=site/view-floor&floor_id='+INTERSECTED.floorid;
            }
        }

        function addMesh(id, position) {
            var position = position || new THREE.Vector3();
            var geometry = new THREE.BoxGeometry(5,0.8,5);
            var material = new THREE.MeshLambertMaterial( { color: 0xfeb74c, map: texture } );
            var mesh = new THREE.Mesh(geometry, material);
            mesh.floorid = id;
            mesh.material.map.wrapS = THREE.RepeatWrapping;
            mesh.material.map.wrapT = THREE.RepeatWrapping;
            mesh.material.map.repeat.set(5, 1);
            mesh.position.copy(position);
            scene.add(mesh);

        }
    }

    window.onload = load;
</script>