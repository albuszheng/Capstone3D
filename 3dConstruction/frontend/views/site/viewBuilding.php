<?php

/* @var $this yii\web\View */
/* @var $building \common\models\Building */
/* @var $canEdit boolean */

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

    <div id="operations" class="btn-group" style="display: none">
        <input type="file" name="file" id="importFile"/>
        <button onclick="importBuilding()" class="btn btn-sm btn-default">导入</button>
        <button onclick="exportBuilding()" class="btn btn-sm btn-default">导出</button>
    </div>
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
        if (<?php
            if($canEdit) {
                echo 1;
            } else {
                echo 0;
            } ?>) {
            $('#operations').css('display', 'block');
        }

        var mouse = new THREE.Vector2(), INTERSECTED;
        var floors = [];

        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        camera.position.set( 4, 4, 13 );
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

        var texture = new THREE.TextureLoader().load( "img/build4.png" );

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
                    INTERSECTED.material.emissive.setHex( 0x1BE634 );
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
            var material = new THREE.MeshLambertMaterial( {  map: texture } );
            var mesh = new THREE.Mesh(geometry, material);
            mesh.floorid = id;
            mesh.material.map.wrapS = THREE.RepeatWrapping;
            mesh.material.map.wrapT = THREE.RepeatWrapping;
            mesh.material.map.repeat.set(3, 1);
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
            var roofMaterial = new THREE.MeshBasicMaterial({map:new THREE.TextureLoader().load( "img/roof3.png" )});
            matArray.push(mapMaterial);
            matArray.push(mapMaterial);
            matArray.push(roofMaterial);
            matArray.push(mapMaterial);
            matArray.push(mapMaterial);
            matArray.push(new THREE.MeshBasicMaterial({}));
            var material = new THREE.MeshFaceMaterial(matArray);
            var mesh = new THREE.Mesh(geometry, material);
            mesh.position.copy(position);
            scene.add(mesh);
        }
    }

    function importBuilding() {
        if (typeof FileReader) {
            var file = document.getElementById('importFile').files[0];
            if (file) {
                var reader = new FileReader();
                reader.readAsText(file, 'utf-8');
                reader.onload = function (e) {
                    var data = JSON.parse(this.result);
                    var changeFloor = false;
                    var change = true;

                    if ((data.width !== <?= $building->width?>) || (data.height !== <?= $building->height?>)) {
                        if(confirm("楼层面积不一致,是否继续?点击确定更改楼层面积,点击取消放弃此次操作!")) {
                            if (data.floor !== <?= $building->floor?>) {
                                if(confirm("楼层数不一致,是否继续?点击确定更改楼层数,点击取消不更改!")) {
                                    changeFloor = true;
                                }
                            }
                        } else {
                            change = false;
                        }
                    } else {
                        if (data.floor !== <?= $building->floor?>) {
                            if(confirm("楼层数不一致,是否继续?点击确定更改楼层数,点击取消不更改!")) {
                                changeFloor = true;
                            }
                        }
                    }

                    if (change) {
                        $.ajax({
                            type: 'post',
                            data: {data:JSON.stringify(data), id: <?= $building->id?>, changeFloor: changeFloor},
                            url: 'index.php?r=site/import-building',
                            success: function (data) {
                                if (data.result) {
                                    location.reload();
                                }
                            },

                            error: function (xhr) {
                                console.log(xhr.responseText);
                            }

                        });
                    }
                }
            } else {
                alert("请选择规范的文件导入!");
            }


        } else {
            alert("您的浏览器不支持此功能!");
        }

    }

    function exportBuilding() {
        $.ajax({
            type: 'post',
            data: {id: <?= $building->id ?>},
            url: 'index.php?r=site/export-building',
            success: function (data) {
                var exporter = new SceneExport();
                var sceneJSON = exporter.parseBuildingRomms(data.rooms, <?= $building->width?>, <?= $building->height?>, <?= $building->floor?>);
                var a = window.document.createElement('a');
                a.href = window.URL.createObjectURL(new Blob([JSON.stringify(sceneJSON)], {type: 'text/dta'}));
                a.download = 'test.dta';
                a.target = '_blank';

                document.body.appendChild(a);
                a.click();

                document.body.removeChild(a);
            },

            error: function (xhr) {
                console.log(xhr.responseText);
            }

        });

    }

    window.onload = load;
</script>