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

    <div id="canvas">
    </div>

</div>


<script type="text/javascript" src="js/three.js"></script>
<script type="text/javascript" src="js/ThreeBSP.js"></script>
<script type="text/javascript" src="js/FirstPersonControls.js"></script>
<script type="text/javascript" src="js/ColladaLoader.js"></script>
<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/SceneExport.js"></script>

<script type="text/javascript">
    var clock, scene, camera, renderer, controls;

    var width = $('#canvas').width();
    var height = $('#canvas').height();

    // 初始化
    function init() {
        clock = new THREE.Clock();
        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);

        renderer = new THREE.WebGLRenderer();
        renderer.setClearColor(new THREE.Color(0x000, 1.0));
        renderer.setSize(width, height);

        camera.position.x = 1;
        camera.position.y = 0.5;
        camera.position.z = 2;
        camera.lookAt(new THREE.Vector3(0, 0, 0));

        controls = new THREE.FirstPersonControls(camera);
        controls.lookSpeed = 0.1;
        controls.movementSpeed = 5;
        controls.noFly = true;
        controls.lookVertical = true;
        controls.constrainVertical = true;
        controls.verticalMin = 1.0;
        controls.verticalMax = 2.0;
        controls.lon = -180;
        controls.lat = 0;

        var ambientLight = new THREE.AmbientLight( 0xffffff );
        scene.add( ambientLight );
        var directionalLight = new THREE.DirectionalLight( 0xffffff );
        directionalLight.position.set( 0, 0, 0 ).normalize();
        scene.add( directionalLight );
    }

    function render() {
        controls.update(clock.getDelta());
        renderer.clear();
        requestAnimationFrame(render);
        renderer.render(scene, camera);
    }

    // 加载场景
    function load() {
        var data = null;
        if (<?php echo $data ?> !== null) {
            data = <?php echo $data ?>;
        }


        if (data !== null) {
            init();
            document.getElementById("canvas").appendChild(renderer.domElement);

            if (data.type === "scene") {
                loadFloor(data.floor);
                loadWall(data.wall);
                loadObject(data.objects);

            }
            render();
            console.log('load');
        }
    }

    // 加载地板
    function loadFloor(data) {
        // plane
        var planeGeometry = new THREE.PlaneGeometry( data.width, data.height, 0, 0 );
        var material = new THREE.MeshBasicMaterial();

        $.ajax({
            type:'post',
            data:{id:data.id},
            url:'<?php echo Yii::$app->getUrlManager()->createUrl('/site/model') ?>',

            success:function(data) {
                var url = 'model/images/' + data.model.url2d;
                if (url !== null) {
                    var texture = new THREE.TextureLoader().load(url);
                    material.map = texture;
                }

                material.side = THREE.DoubleSide;
                var plane = new THREE.Mesh( planeGeometry, material );
                plane.rotateX(-Math.PI/2);
                scene.add( plane );
            },

            error:function(xhr) {
                console.log(xhr.responseText);
            }

        });

    }

    // 加载墙壁
    function loadWall(data) {
        $.each(data, function (index, object) {
            var group = new THREE.Object3D();

            $.ajax({
                type:'post',
                data:{id:object.id},
                url:'<?php echo Yii::$app->getUrlManager()->createUrl('/site/model') ?>',

                success:function(data) {
                    var url = 'model/images/' + data.model.url2d;
                    var wallTexture = new THREE.TextureLoader().load(url);
                    var wallMaterial = new THREE.MeshBasicMaterial({map: wallTexture});
                    wallMaterial.side = THREE.DoubleSide;

                    var size = [object.size[0], 4, object.size[1]];
                    var position = [object.position[0]-10, 2, object.position[1]-10];
                    var rotation = [0, -object.rotation*Math.PI ,0];

                    var wallBSP = new ThreeBSP(addWall(size, position, rotation, url));
                    if (object.doors !== undefined) {
                        $.each(object.doors, function(index, model) {
                            wallBSP = loadDoor(model, wallBSP, rotation, group);
                        });
                    }

                    if (object.windows !== undefined) {
                        $.each(object.windows, function(index, model) {
                            wallBSP = loadWindow(model, wallBSP, rotation, group);
                        });
                    }

                    var wall = wallBSP.toMesh();
                    wall.geometry.computeFaceNormals();
                    wall.geometry.computeVertexNormals();
                    var result = new THREE.Mesh(wall.geometry, wallMaterial);
                    result.material.map.wrapS = THREE.RepeatWrapping;
                    result.material.map.wrapT = THREE.RepeatWrapping;
                    result.material.map.repeat.set(size[0]/4, size[1]/4);
                    result.position.fromArray(position);
                    result.rotation.fromArray(rotation);
                    group.add(result);
                    scene.add(group);
                },

                error:function(xhr) {
                    console.log(xhr.responseText);
                }

            });
        });

    }

    // 加载家具模型
    function loadObject(data) {
        $.each(data, function (index, object) {
            var position3D = [object.position[0]-10, 0, object.position[1]-10];
            var rotation3D = [-Math.PI/2 , 0, -object.rotation * Math.PI];

            loadModel(object.id, position3D, rotation3D, scene);
        });
    }

    // 加载模型
    function loadModel(id, position, rotation, scene) {
        $.ajax({
            type:'post',
            data:{id:id},
            url:'<?php echo Yii::$app->getUrlManager()->createUrl('/site/model') ?>',

            success:function(data) {
                var loader = new THREE.ColladaLoader();
                loader.load(
                    'model/' + data.model.url3d,
                    function ( collada ) {
                        var voxel = collada.scene;
                        voxel.rotation.fromArray(rotation);
                        voxel.position.fromArray(position);
                        voxel.scale.fromArray(data.model.scale.split(','));
                        voxel.id = id;
                        scene.add( voxel );
                    }
                );
            },

            error:function(xhr) {
                console.log(xhr.responseText);
            }

        });

    }

    // 加载门
    function loadDoor(model, wallBSP, wallrotation, group) {
        $.ajax({
            type:'post',
            data:{id:model.id},
            url:'<?php echo Yii::$app->getUrlManager()->createUrl('/site/model') ?>',
            async : false,
            success:function(data) {
                var rotation = -model.rotation * Math.PI;
                var position3D = [
                    model.position[0] - 10 + Math.sin(rotation) * 0.05,
                    0,
                    model.position[1] - 10 + Math.cos(rotation) * 0.05];
                var rotation3D = [-Math.PI/2 , 0, rotation];
                var size = data.model.size.split(',');
                var bspposition = convertBSPPosition(rotation3D[2], position3D, size);
                loadModel(model.id, position3D, rotation3D, group);

                var modelBSP = new ThreeBSP(addWall(size, bspposition, wallrotation));
                wallBSP = wallBSP.subtract(modelBSP);

            },

            error:function(xhr) {
                console.log(xhr.responseText);
            }

        });

        return wallBSP;

    }

    // 加载窗
    function loadWindow(model, wallBSP, wallrotation, group) {
        $.ajax({
            type:'post',
            data:{id:model.id},
            url:'<?php echo Yii::$app->getUrlManager()->createUrl('/site/model') ?>',
            async : false,
            success:function(data) {
                var defaultRotation = 0.5 * Math.PI;
                var rotation = -model.rotation * Math.PI;
                var position3D = [
                    model.position[0] - 10 - Math.sin(rotation)*0.05,
                    2,
                    model.position[1] - 10 - Math.cos(rotation)*0.05];
                var rotation3D = [-Math.PI/2 , 0, rotation-defaultRotation];
                var size = data.model.size.split(',');
                var tmpsize = size.slice();
                if (isZero(Math.cos(defaultRotation))) {
                    tmpsize[0] = size[2];
                    tmpsize[2] = size[0];
                }
                var bspposition = convertBSPPosition(rotation3D[2], position3D, tmpsize);
                loadModel(model.id, position3D, rotation3D, group);

                var modelBSP = new ThreeBSP(addWall(size, bspposition, wallrotation));
                wallBSP = wallBSP.subtract(modelBSP);
            },

            error:function(xhr) {
                console.log(xhr.responseText);
            }

        });

        return wallBSP;
    }

    // 添加墙壁
    function addWall(size, position, rotation, url) {
        var geometry = new THREE.BoxGeometry(size[0], size[1], size[2]);
        var material = new THREE.MeshBasicMaterial();
        material.side = THREE.DoubleSide;
        var mesh = new THREE.Mesh(geometry, material);
        mesh.position.fromArray(position);
        mesh.rotation.fromArray(rotation);

        if (url !== undefined) {
            var texture = new THREE.TextureLoader().load(url);
            material.map = texture;
            mesh.material.map.wrapS = THREE.RepeatWrapping;
            mesh.material.map.wrapT = THREE.RepeatWrapping;
            mesh.material.map.repeat.set(size[0]/4, size[1]/4);
        }

        return mesh;
    }

    // 转换BSP坐标
    function convertBSPPosition(rotation, position, size) {
        var x = size[0] * Math.cos(rotation) - size[2] * Math.sin(rotation);
        var y = size[1];
        var z = -size[0] * Math.sin(rotation) - size[2] * Math.cos(rotation);
        return [position[0] + x / 2, position[1] + y / 2, position[2] + z / 2];
    }

    // 小于偏差值认为等于0
    function isZero(number) {
        if (Math.abs(number) <= CONST.DEVIATION) {
            return true;
        }
        return false;
    }

    function convertStringtoArray(str) {
        var s = str.split(',');
        var size = [];
        for (var i = 0; i < s.length; i++) {
            size[i] = Number(s[i]);
        }
        return size;
    }

    window.onload = load;
</script>