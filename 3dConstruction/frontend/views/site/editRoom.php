<?php

/* @var $this yii\web\View */
/* @var $room \common\models\Room */
/* @var $modules \common\models\Module[] */

use yii\helpers\Html;
use frontend\assets\ThreeAsset;

ThreeAsset::register($this);

$this->title = 'Edit Room'.$room->room_no;
$this->params['breadcrumbs'][] = ['label' => 'Overview', 'url' => ['overview']];
$this->params['breadcrumbs'][] = ['label' => 'View Building', 'url' => ['view-building', 'id'=>$room->building_id]];
$this->params['breadcrumbs'][] = ['label' => 'View Floor'.$room->floor_no, 'url' => ['view-floor', 'floor'=>$room->floor_no, 'id'=>$room->building_id]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-view-room">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>This is one room:</p>

    <div id="webgl-output">
        <div id="2dbutton">
            <button onclick="save()">保存</button>
            <button onclick="load()">加载</button>
            <button onclick="edit()">编辑</button>
            <button onclick="see()">查看</button>
            <button onclick="to3d()">查看3d场景</button>
            <button onclick="importRoom()">导入</button>
            <button onclick="exportRoom()">导出</button>
<!--            <a id='save-btn' href="data:text/paint; utf-8, 。" download="scene.txt">保存</a>-->

        </div>

        <div id="3dbutton" style="visibility: hidden">
            <button id="to2dbutton">查看2d场景</button>
        </div>

        <div>
            <button id="1" onclick="addFloor(this.id)">地板0</button>
            <button id="2" onclick="addFloor(this.id)">地板1</button>
            <button id="3" onclick="addFloor(this.id)">地板2</button>
            <button id="4" onclick="addWall(this.id)">墙壁</button>
            <button id="5" onclick="addDoorWindow(this.id, CONST.TYPE.DOOR)">门</button>
            <button id="6" onclick="addDoorWindow(this.id, CONST.TYPE.WINDOW)">窗</button>
            <button id="7" onclick="addFurniture(this.id)">床</button>
            <button id="8" onclick="addFurniture(this.id)">衣橱</button>
            <button id="9" onclick="addFurniture(this.id)">床头柜</button>
            <button id="12" onclick="addFurniture(this.id)">沙发</button>
            <button id="11" onclick="addFurniture(this.id)">桌子</button>
            <button id="10" onclick="addFurniture(this.id)">电视</button>
            <button id="13" onclick="addDoorWindow(this.id, CONST.TYPE.SENSOR)">传感器</button>
        </div>
        <?php
        foreach ($modules as $module): ?>
            <button id=<?=$module->id?> onclick=importModule(<?= $module->data ?>)><?= $module->name ?></button>
        <?php endforeach;?>
        <div>

        </div>
        <div>
            <button onclick="rotateModel()">旋转</button>
            <button onclick="deleteModel()">删除</button>
            <button onclick="clearModel()">清空</button>
        </div>
        <div id="info">
            <ul>
                <li id="pos">position:</li>
                <li id="rot">rotation:</li>
            </ul>
        </div>

        <div id="canvas2d">
        </div>
        <div id="canvas3d"></div>
    </div>
</div>

<script type="text/javascript" src="js/pixi.js"></script>
<script type="text/javascript" src="js/three.js"></script>
<script type="text/javascript" src="js/ThreeBSP.js"></script>
<script type="text/javascript" src="js/FirstPersonControls.js"></script>
<script type="text/javascript" src="js/ColladaLoader.js"></script>
<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/SceneExport.js"></script>
<script type="text/javascript" src="js/SceneLoad.js"></script>

<script type="text/javascript">
    /**
     * TODO
     * 1.collision detection
     */

    var width2d = $('#canvas2d').width();
    var width3d = $('#canvas3d').width();
    var height2d = $('#canvas2d').height();
    var height3d = $('#canvas3d').height();
    var pos = document.getElementById("pos");
    var rot = document.getElementById("rot");
    var step = Math.min(width2d, height2d);
    var models = [];
    var data = null;

    var stage, floor, walls, group, graph;
    var selected = undefined;
    var isEdit = false;

    function start() {
        if (<?= $room->id ?> !== null) {
            data = <?= (isset($room->data) && !(is_null($room->data)) && !(empty($room->data))) ? $room->data : 'null' ?>;

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
            if (data) {
                if (data.type === "scene") {
                    load(data);
                    console.log('load');
                }
            } else {
                var room_size = <?= (isset($room->size) && !(is_null($room->size))) ? "'" . $room->size . "'" : '0,0' ?>;
                var size = room_size.split(',');
                var exporter = new SceneExport();
                var sceneJSON = exporter.parseInitRoom(size[0], size[1]);
                data = sceneJSON;
                load(sceneJSON);
            }

        } else {
            document.getElementById('webgl-output').innerHTML='No Room';
        }
    }

    // 网格线
    function createLine() {
        graph = new PIXI.Graphics();
        graph.lineStyle(1, 0x000, 1);
        for (var i = step; i < width2d; i+=step) {
            graph.moveTo(i, 0);
            graph.lineTo(i, width2d);

            graph.moveTo(0, i);
            graph.lineTo(width2d, i);
        }
        graph.visible = false;
        stage.addChild(graph);
    }

    /**
     * 保存场景
     */
    function save() {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        if (selected !== undefined) {
            selected.alpha = 1;
            selected = undefined;
        }

        var exporter = new SceneExport();
        var sceneJSON = exporter.parse(floor, walls, group, step);
        $.ajax({
            type:'post',
            data:{id:<?= $room->id ?>, data:JSON.stringify(sceneJSON)},
            url:'<?php echo Yii::$app->getUrlManager()->createUrl('/site/update-room') ?>',
            async : false,
            success:function(data) {
                if (data.result === true) {
                    console.log('save success');
                } else {
                    console.log('save fail');
                }

            },

            error:function(xhr) {
                console.log(xhr.responseText);
            }

        });

        data = sceneJSON;
        see();
    }

    /**
     * 加载场景
     */
    function load(scene, width, height) {
        if (scene === undefined) {
            scene = data;
        }

        var width = width || scene.floor.width;
        var height = height || scene.floor.height;
        step = Math.min(width2d/width, height2d/height);

        if (isEdit) {
            if(confirm("是否保存当前场景?")) {
                save();
            }
        }

        var loader = new SceneLoad();
        stage = loader.load2d(scene, width2d, height2d, document.getElementById('canvas2d'), models, step);
        floor = stage.getChildAt(0);
        walls = stage.getChildAt(1);
        group = stage.getChildAt(2);

        createLine();
        updateInfo();
    }

    /**
     * 编辑场景
     */
    function edit() {
        isEdit = true;
        graph.visible = true;

        $.each(walls.children, function (index, object) {
            object.interactive = true;
            object.buttonMode = true;
        });

        $.each(group.children, function (index, object) {
            object.interactive = true;
            object.buttonMode = true;
        });
    }

    /**
     * 查看场景
     */
    function see() {
        isEdit = false;
        graph.visible = false;
        $("#canvas2d").unbind('mousedown', dragStart);

        if (selected !== undefined) {
            selected.alpha = 1;
            selected = undefined;
        }

        $.each(walls.children, function (index, object) {
            object.interactive = false;
        });

        $.each(group.children, function (index, object) {
            object.interactive = false;
        });

    }

    /**
     * 查看3d场景
     */
    function to3d() {
        if (isEdit) {
            console.log("当前编辑模式,无法查看3d场景");
            return;
        }

        var exporter = new SceneExport();
        var sceneJSON = exporter.parse(floor, walls, group, step);

        $('#2dbutton').css('visibility', 'hidden');
        $('#3dbutton').css('visibility', 'visible');
        $('#canvas2d').css('display', 'none');
        $('#canvas3d').css('display', 'block');
        document.getElementById('to2dbutton').onclick = function() {
          to2d(sceneJSON);
        };

        var loader = new SceneLoad();
        loader.load3d(sceneJSON, width3d, height3d, document.getElementById('canvas3d'), models);
    }

    /**
     * 查看2d场景
     */
    function to2d(data) {
        $('#2dbutton').css('visibility', 'visible');
        $('#3dbutton').css('visibility', 'hidden');
        $('#canvas3d').css('display', 'none');
        $('#canvas2d').css('display', 'block');

        var loader = new SceneLoad();
        stage = loader.load2d(data, width2d, height2d, document.getElementById('canvas2d'), models);
        floor = stage.getChildAt(0);
        walls = stage.getChildAt(1);
        group = stage.getChildAt(2);

        step = Math.min(width2d/data.floor.width, height2d/data.floor.height);
        createLine();
    }

    function importModule(data) {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        var loader = new SceneLoad();
        stage = loader.load2d(data, width2d, height2d, document.getElementById('canvas2d'), models, step);
        floor = stage.getChildAt(0);
        walls = stage.getChildAt(1);
        group = stage.getChildAt(2);
        createLine();
        edit();
    }

    function importRoom() {
        // TODO get data
        var data = {"version":"1.0.0","type":"scene","floor":{"type":"floor","width":20,"height":20,"id":"1"},"wall":[{"type":"wall","id":"4","size":[19.9,0.1],"position":[0.1,10],"rotation":1.5,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[19.9,0.1],"position":[19.9,10],"rotation":0.5,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[19.9,0.1],"position":[10,19.9],"rotation":1,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[19.9,0.1],"position":[10,0.1],"rotation":0,"doors":[],"windows":[],"sensors":[]}],"objects":[{"type":"furniture","id":"7","position":[10,10],"rotation":0}]};
//        console.log(data); //sceneJSON
        importModule(data);
    }

    function exportRoom() {
        // TODO to file
        var exporter = new SceneExport();
        var sceneJSON = exporter.parse(floor, walls, group, step);
        console.log(sceneJSON);
        alert(sceneJSON);
        return sceneJSON;
    }

    /**
     * 添加地板
     */
    function addFloor(id) {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        $("#canvas2d").unbind('mousedown', dragStart);
        floor.texture = PIXI.Texture.EMPTY;

        var model = findModelById(id);
        if (model !== null) {
            var url = model.url2d;
            if (url !== "null") {
                floor.texture = PIXI.Texture.fromImage('model/images/floor/' + url);
            }
            floor.id = id;
        }
    }

    /**
     * 添加墙壁
     */
    var mouseX, mouseY;
    var wallid;
    function addWall(id) {
        $("#canvas2d").unbind('mousedown', dragStart);
        $.each(walls.children, function (index, object) {
            object.interactive = false;
        });

        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        $("#canvas2d").bind('mousedown', dragStart);
        wallid = id;
    }

    // 开始墙壁拖动事件
    var dragStart = function(e) {
        mouseX = e.pageX - e.currentTarget.offsetLeft;
        mouseY = e.pageY - e.currentTarget.offsetTop;

        $("#canvas2d")
            .bind('mousemove', drag)
            .bind('mouseup', dragEnd);
    };

    // 墙壁拖动事件
    var drag = function(e) {
    };

    // 结束墙壁拖动事件
    var dragEnd = function(e) {
        var x = e.pageX - e.currentTarget.offsetLeft;
        var y = e.pageY - e.currentTarget.offsetTop;

        var position = [];
        var rotation = 0;
        var size = [];

        if (Math.abs(x - mouseX) < Math.abs(y - mouseY)) {
            x = mouseX;
            size = [Math.abs(y - mouseY), 0.1 * step];
            rotation = Math.PI / 2;
            position = [x, (y + mouseY) / 2];
        } else {
            y = mouseY;
            size = [Math.abs(x - mouseX), 0.1 * step];
            position = [(x + mouseX) / 2, y];
        }

        $.each(walls.children, function (index, object) {
            object.interactive = true;
        });

        var wall = createWall(wallid, position, rotation, size);
        selectMode(wall, CONST.TYPE.WALL);
        updateInfo(selected);

        $("#canvas2d")
            .unbind('mousedown', dragStart)
            .unbind('mousemove', drag)
            .unbind('mouseup', dragEnd);
    };


    /**
     * 添加门窗
     * @param id
     * @param type
     */
    function addDoorWindow(id, type) {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        if (selected === undefined || selected.type !== CONST.TYPE.WALL) {
            console.log("请选择一面墙壁");
            return;
        }

        $("#canvas2d").unbind('mousedown', dragStart);
        var parent = selected;
        var position = [parent.position.x, parent.position.y];
        var model = createDoorWindow(id, position, parent.rotation);
        model.type = type;
        model.id = id;
        model.wall = parent;
        parent.children.push(model);
        selectMode(model, type);
        updateInfo(selected);
        console.log(parent.children);
    }

    /**
     * 添加家具模型
     * @param id
     */
    function addFurniture(id) {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        $("#canvas2d").unbind('mousedown', dragStart);
        var model = createFurniture(id, [width2d/2, height2d/2], 0);
        if (model !== null) {
            selectMode(model, CONST.TYPE.FURNITURE);
            updateInfo(selected);
        }
    }

    // 开始模型拖动事件
    function onDragStart(event) {
        selectMode(this, event.target.type);
        this.data = event.data;
        this.dragging = true;

        updateInfo(selected);
    }

    // 结束模型拖动事件
    function onDragEnd() {
        this.dragging = false;
        this.data = null;

        var bounds = new PIXI.Rectangle(0, 0, width2d, height2d);
        switch (selected.type) {
            case CONST.TYPE.DOOR:
            case CONST.TYPE.WINDOW:
            case CONST.TYPE.SENSOR:
                var wall = selected.wall;
                var outBounds;
                if (isZero(Math.sin(selected.rotation))) {
                    outBounds = wall.getBounds().width > wall.width;
                } else {
                    outBounds = wall.getBounds().height > wall.width;
                }
                if (outBounds) {
                    selected.position.set(wall.position.x, wall.position.y);
                }

                break;
            case CONST.TYPE.WALL:
                if (isOut(selected.getBounds(), bounds)) {
                    $.each(selected.children, function (index, object) {
                        var offset = object.position.x - selected.position.x + object.position.y - selected.position.y;
                        object.position.x = Math.abs(Math.cos(object.rotation)) * offset + width2d / 2;
                        object.position.y = Math.abs(Math.sin(object.rotation)) * offset + height2d / 2;
                    });
                    selected.position.set(width2d / 2, height2d / 2);
                }
                break;
            case CONST.TYPE.FURNITURE:
                if (isOut(selected.getBounds(), bounds)) {
                    selected.position.set(width2d / 2, height2d / 2);
                }
                break;
            default:
                console.log("unknown model");
                break;
        }
        updateInfo(selected);
    }

    // 模型拖动事件
    function onDragMove(event) {
        if (this.dragging) {
            var newPosition = this.data.getLocalPosition(this.parent);
            switch (event.target.type) {
                case CONST.TYPE.DOOR:
                case CONST.TYPE.WINDOW:
                case CONST.TYPE.SENSOR:
                    if (isZero(Math.abs(Math.sin(selected.rotation)))) {
                        this.position.x = newPosition.x;
                    } else {
                        this.position.y = newPosition.y;
                    }
                    break;
                case CONST.TYPE.WALL:
                    $.each(selected.children, function (index, object) {
                        object.position.x += newPosition.x - selected.position.x;
                        object.position.y += newPosition.y - selected.position.y;
                    });
                case CONST.TYPE.FURNITURE:
                    this.position.x = newPosition.x;
                    this.position.y = newPosition.y;
                    break;
                default:
                    console.log("unknown model");
                    break;
            }
            updateInfo(selected);

        }
    }

    // 创建墙壁
    function createWall(id, position, rotation, size) {
        var model = findModelById(id);
        var wall = null;

        if (model !== null) {
            var texture = PIXI.Texture.fromImage('model/images/wall/' + model.url2d);
            wall = new PIXI.Sprite(texture);
            wall.interactive = true;
            wall.buttonMode = true;
            wall.anchor.set(0.5, 0.5);
            wall.position.set(position[0], position[1]);
            wall.rotation = rotation;
            wall.width = size[0];
            wall.height = size[1];
            wall.type = CONST.TYPE.WALL;
            wall.id = id;

            wall
                .on('mousedown', onDragStart)
                .on('mouseup', onDragEnd)
                .on('mouseupoutside', onDragEnd)
                .on('mousemove', onDragMove);


            walls.addChild(wall);
        }

        return wall;
    }

    // 创建门窗
    function createDoorWindow(id, position, rotation) {
        var model = null;
        var doorwindow = findModelById(id);

        if (doorwindow !== null) {
            var texture = PIXI.Texture.fromImage('model/plan/' + doorwindow.url2d);
            model = new PIXI.Sprite(texture);
            model.interactive = true;
            model.buttonMode = true;
            model.anchor.set(0, 0.5);
            model.position.set(position[0], position[1]);
            var size = doorwindow.size.split(',');
            model.width = size[0] * step;
            model.height = size[2] * step * 2;
            model.rotation = rotation;
            model.id = id;

            model
                .on('mousedown', onDragStart)
                .on('mouseup', onDragEnd)
                .on('mouseupoutside', onDragEnd)
                .on('mousemove', onDragMove);

            walls.addChild(model);
        }

        return model;
    }

    // 创建家具模型
    function createFurniture(id, position, rotation) {
        var furniture = findModelById(id);
        var model = null;

        if (furniture !== null) {
            var texture = PIXI.Texture.fromImage('model/plan/' + furniture.url2d);
            model = new PIXI.Sprite(texture);
            model.interactive = true;
            model.buttonMode = true;
            model.anchor.set(0, 1);
            model.position.set(position[0], position[1]);
            model.rotation = rotation * Math.PI;
            var size = furniture.size.split(',');
            model.width = size[0] * step;
            model.height = size[1] * step;
            model.type = CONST.TYPE.FURNITURE;
            model.id = id;

            model
                .on('mousedown', onDragStart)
                .on('mouseup', onDragEnd)
                .on('mouseupoutside', onDragEnd)
                .on('mousemove', onDragMove);

            group.addChild(model);
        }

        return model;
    }

    /**
     * 旋转模型
     */
    function rotateModel() {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        if (selected !== undefined) {
            switch (selected.type) {
                case CONST.TYPE.DOOR:
                case CONST.TYPE.WINDOW:
                case CONST.TYPE.SENSOR:
                    console.log("请对门窗所在墙壁进行操作");
                    break;
                case CONST.TYPE.WALL:
                    $.each(selected.children, function (index, object) {
                        var offset = object.position.x - selected.position.x + object.position.y - selected.position.y;
                        object.rotation = (object.rotation + Math.PI / 2) % CONST.PI_2;
                        object.position.x = -Math.abs(Math.cos(object.rotation)) * offset + selected.position.x;
                        object.position.y = Math.abs(Math.sin(object.rotation)) * offset + selected.position.y;
                    });
                case CONST.TYPE.FURNITURE:
                    selected.rotation = (selected.rotation + Math.PI / 2) % CONST.PI_2;
                    updateInfo(selected);
                    break;
                default:
                    console.log("unknown type");
                    break;
            }
        }
    }

    /**
     * 删除模型
     */
    function deleteModel() {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        if (selected !== undefined) {
            switch (selected.type) {
                case CONST.TYPE.DOOR:
                case CONST.TYPE.WINDOW:
                case CONST.TYPE.SENSOR:
                    var object = selected.wall.children;
                    object.splice(object.indexOf(selected), 1);
                    walls.removeChild(selected);
                    break;
                case CONST.TYPE.WALL:
                    $.each(selected.children, function (index, object) {
                        walls.removeChild(object);
                    });
                    walls.removeChild(selected);
                    updateInfo();
                    break;
                case CONST.TYPE.FURNITURE:
                    group.removeChild(selected);
                    updateInfo();
                    break;
                default:
                    console.log("unknown model");
                    break;
            }
        }
        selected = undefined;
    }

    /**
     * 清空模型
     */
    function clearModel() {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        floor.texture = PIXI.Texture.EMPTY;
        floor.id = 1;
        walls.removeChildren(0, walls.children.length);
        group.removeChildren(0, group.children.length);
        updateInfo();
    }

    // 更新模型显示信息
    function updateInfo(model) {
        if (model === undefined) {
            pos.innerHTML = "position:";
            rot.innerHTML = "rotation:";
            return;
        }

        pos.innerHTML = "position:" + model.position.x + "," + model.position.y;
        model.rotation = model.rotation % CONST.PI_2;
        rot.innerHTML = "rotation:" + model.rotation/(Math.PI/2)*90+"°";
    }

    // 判断模型是否超出边界
    function isOut(model, bounds) {
        if (model.x < bounds.x || model.x + model.width > bounds.x + bounds.width) {
            return true;
        }

        if (model.y < bounds.y || model.y + model.height > bounds.y + bounds.height) {
            return true;
        }
        return false;
    }

    // 选中模型
    function selectMode(model, type) {
        if (selected !== undefined) {
            selected.alpha = 1;

        }
        selected = model;
        selected.alpha = 0.5;
        selected.type = type;
    }

    // 小于偏差值认为等于0
    function isZero(number) {
        if (Math.abs(number) <= CONST.DEVIATION) {
            return true;
        }
        return false;
    }

    //根据id查找模型信息
    function findModelById(id) {
        for (var i = 0; i < models.length; i++) {
            if (id == models[i].id) {
                return models[i];
            }
        }
        return null;
    }

    window.onload = start;

</script>