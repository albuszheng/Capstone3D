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
        var isShiftDown = false;
        var objects = [];
        var camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 10000);
        camera.position.set(300, 700, 1400);
        camera.lookAt(new THREE.Vector3());

        var scene = new THREE.Scene();

        // roll-over helpers
        var rollOverGeo = new THREE.BoxGeometry(50, 50, 50);
        var rollOverMaterial = new THREE.MeshBasicMaterial({color: 0xff0000, opacity: 0.5, transparent: true});
        var rollOverMesh = new THREE.Mesh(rollOverGeo, rollOverMaterial);
        scene.add(rollOverMesh);

        // cubes
        var cubeGeo = new THREE.BoxGeometry(50, 50, 50);
        var cubeMaterial = new THREE.MeshLambertMaterial({
            color: 0xfeb74c,
            map: new THREE.TextureLoader().load("model/images/wall/square-outline-textured.png")
        });

        // grid
        var size = 500, step = 100;
        var geometry = new THREE.Geometry();
        for (var i = -size; i <= size; i += step) {

            geometry.vertices.push(new THREE.Vector3(-size, 0, i));
            geometry.vertices.push(new THREE.Vector3(size, 0, i));

            geometry.vertices.push(new THREE.Vector3(i, 0, -size));
            geometry.vertices.push(new THREE.Vector3(i, 0, size));

        }

        var material = new THREE.LineBasicMaterial({color: 0x000000, opacity: 0.2, transparent: true});
        var line = new THREE.LineSegments(geometry, material);
        scene.add(line);

        var raycaster = new THREE.Raycaster();
        var mouse = new THREE.Vector2();

        var geometry = new THREE.PlaneBufferGeometry(1000, 1000);
        geometry.rotateX(-Math.PI / 2);

        var plane = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial({visible: false}));
        scene.add(plane);
        objects.push(plane);

        // Lights
        var ambientLight = new THREE.AmbientLight(0x606060);
        scene.add(ambientLight);

        var directionalLight = new THREE.DirectionalLight(0xffffff);
        directionalLight.position.set(1, 0.75, 0.5).normalize();
        scene.add(directionalLight);

        var renderer = new THREE.WebGLRenderer({antialias: true});
        renderer.setClearColor(0xf0f0f0);
        renderer.setSize(width, height);
        canvas.appendChild(renderer.domElement);

        document.addEventListener('mousemove', onDocumentMouseMove, false);
        document.addEventListener('mousedown', onDocumentMouseDown, false);
        document.addEventListener('keydown', onDocumentKeyDown, false);
        document.addEventListener('keyup', onDocumentKeyUp, false);

        render();

        function onDocumentMouseMove(event) {
            event.preventDefault();
            mouse.x = ( (event.pageX - event.target.offsetLeft) / width ) * 2 - 1;
            mouse.y = - ( (event.pageY - event.target.offsetTop) / height ) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);
            var intersects = raycaster.intersectObjects(objects);

            if (intersects.length > 0) {
                var intersect = intersects[0];
                rollOverMesh.position.copy(intersect.point).add(intersect.face.normal);
                rollOverMesh.position.divideScalar(50).floor().multiplyScalar(50).addScalar(25);
            }

            render();
        }

        function onDocumentMouseDown(event) {
            event.preventDefault();
            mouse.x = ( (event.pageX - event.target.offsetLeft) / width ) * 2 - 1;
            mouse.y = - ( (event.pageY - event.target.offsetTop) / height ) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);
            var intersects = raycaster.intersectObjects(objects);

            if (intersects.length > 0) {
                var intersect = intersects[0];

                // delete cube
                if (isShiftDown) {
                    if (intersect.object != plane) {
                        scene.remove(intersect.object);
                        objects.splice(objects.indexOf(intersect.object), 1);
                    }

                // create cube
                } else {
                    var voxel = new THREE.Mesh(cubeGeo, cubeMaterial);
                    voxel.position.copy(intersect.point).add(intersect.face.normal);
                    voxel.position.divideScalar(50).floor().multiplyScalar(50).addScalar(25);
                    scene.add(voxel);
                    objects.push(voxel);
                }
                render();

            }
        }

        function onDocumentKeyDown(event) {
            switch (event.keyCode) {
                case 16:
                    isShiftDown = true;
                    break;
            }
        }

        function onDocumentKeyUp(event) {
            switch (event.keyCode) {
                case 16:
                    isShiftDown = false;
                    break;
            }
        }

        function render() {
            renderer.render(scene, camera);
        }
    }

    window.onload = load;
</script>