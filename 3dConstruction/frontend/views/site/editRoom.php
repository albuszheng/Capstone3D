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
    <div id="webgl-output">
        <div class="row">
            <div class="control-group col-md-2">
                <div id="info">
                    <ul>
                        <li id="pos">position:</li>
                        <li id="rot">rotation:</li>
                    </ul>
                </div>

                <div id="2dbutton" class="btn-group-vertical btn-group">
                <!-- <div class="btn-group btn-group"> -->
                    <button onclick="save()" class="btn btn-default">保存</button>
                    <button onclick="load()" class="btn btn-default">加载</button>
                    <button onclick="edit()" class="btn btn-default">编辑</button>
                <!-- </div> -->
                <!-- <div class="btn-group btn-group"> -->
                    <button onclick="see()" class="btn btn-default">查看</button>
                    <!-- <input type="file" name="file" id="importFile" class="btn btn-default" /> -->
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">导入 <span class="caret"></span></button>     
                    <ul class="dropdown-menu">
                      <li><input type="file" name="file" id="importFile" /> <button onclick="importRoom()" class="btn btn-default btn-xs">确认</button></li>
                      <!-- <li><button onclick="importRoom()" class="btn btn-default btn-sm">确认</button></li> -->
                    </ul>
                    <button onclick="exportRoom()" class="btn btn-default">导出</button>
                <!-- </div> -->
                </div>

                <div class="btn-group btn-group-sm btn-group-vertical model-list">
                    <button id="1" onclick="addFloor(this.id)" class="btn btn-sm btn-default">地板0</button>
                    <button id="2" onclick="addFloor(this.id)" class="btn btn-sm btn-default">地板1</button>
                    <button id="3" onclick="addFloor(this.id)" class="btn btn-sm btn-default">地板2</button>
                    <button id="4" onclick="addWall(this.id)" class="btn btn-sm btn-default">墙壁</button>
                    <button id="5" onclick="addDoorWindow(this.id, CONST.TYPE.DOOR)" class="btn btn-sm btn-default">门</button>
                    <button id="6" onclick="addDoorWindow(this.id, CONST.TYPE.WINDOW)" class="btn btn-sm btn-default">窗</button>
                    <button id="7" onclick="addFurniture(this.id)" class="btn btn-sm btn-default">床</button>
                    <button id="8" onclick="addFurniture(this.id)" class="btn btn-sm btn-default">衣橱</button>
                    <button id="9" onclick="addFurniture(this.id)" class="btn btn-sm btn-default">床头柜</button>
                    <button id="12" onclick="addFurniture(this.id)" class="btn btn-sm btn-default">沙发</button>
                    <button id="11" onclick="addFurniture(this.id)" class="btn btn-sm btn-default">桌子</button>
                    <button id="10" onclick="addFurniture(this.id)" class="btn btn-sm btn-default">电视</button>
                    <button id="13" onclick="addSensor(this.id)" class="btn btn-sm btn-default">传感器</button>
                </div>
                <br/>
                <div class="btn-group btn-group-vertical model-control">
                    <button onclick="rotateModel()" class="btn btn-default">旋转</button>
                    <button onclick="deleteModel()" class="btn btn-default">删除</button>
                    <button onclick="clearModel()" class="btn btn-default">清空</button>
                </div>


                
                <div class="seperator"></div>

                <div class="btn-group-vertical">
                <?php
                foreach ($modules as $module): ?>
                    <button class="btn btn-default" id=<?=$module->id?> onclick=importModule(<?= $module->data ?>)><?= $module->name ?></button>
                <?php endforeach;?>
                </div>

                <div class="seperator"></div>
                
                <div id="3dbutton" class="view-change">
                    <button id="to3dbutton" onclick="to3d()" class="btn btn-lg btn-link">3D</button>
                    /
                    <button id="to2dbutton" class="btn btn-lg btn-link active" disabled="disabled">2D</button>
                </div>

            </div>

            <div class="col-md-10">
                <div id="canvas2d"></div>
                <div id="canvas3d"></div>
            </div>
        </div>
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
            alert("当前非编辑模式");
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
            if (object.type === CONST.TYPE.SENSOR) {
                object.removeAllListeners();
                object
                    .on('mousedown', onDragStart)
                    .on('mouseup', onDragEnd)
                    .on('mouseupoutside', onDragEnd)
                    .on('mousemove', onDragMove);
            } else {
                object.interactive = true;
                object.buttonMode = true;
            }
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
            if (object.type === CONST.TYPE.SENSOR) {
                object.removeAllListeners();
                object
                    .on('mousedown', onMouseMove);
            } else {
                object.interactive = false;
            }
        });

    }

    /**
     * 查看3d场景
     */
    function to3d() {
        if (isEdit) {
            alert("当前编辑模式,无法查看3d场景");
            return;
        }

        var exporter = new SceneExport();
        var sceneJSON = exporter.parse(floor, walls, group, step);

        // $('#2dbutton').css('visibility', 'hidden');
        // $('#3dbutton').css('visibility', 'visible');
        $('#to3dbutton').attr('disabled','disabled');
        $('#to3dbutton').addClass('active');
        $('#to2dbutton').removeAttr('disabled');
        $('#to2dbutton').removeClass('active');
        $('#canvas2d').css('display', 'none');
        $('#canvas3d').css('display', 'inline-block');
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
        // $('#2dbutton').css('visibility', 'visible');
        // $('#3dbutton').css('visibility', 'hidden');
        $('#to2dbutton').attr('disabled','disabled');
        $('#to2dbutton').addClass('active');
        $('#to3dbutton').removeAttr('disabled');
        $('#to3dbutton').removeClass('active');
        $('#canvas3d').css('display', 'none');
        $('#canvas2d').css('display', 'inline-block');

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
            alert("当前非编辑模式");
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
        if (!isEdit) {
            alert("当前非编辑模式");
            return;
        }

        if (typeof FileReader) {
            var file = document.getElementById('importFile').files[0];
            if (file) {
                var reader = new FileReader();
                reader.readAsText(file, 'utf-8');
                reader.onload = function (e) {
                    importModule(JSON.parse(this.result));
                }
            } else {
                alert("请选择规范的文件导入!");
            }


        } else {
            alert("您的浏览器不支持此功能!");
        }

    }

    function exportRoom() {
        var exporter = new SceneExport();
        var sceneJSON = exporter.parse(floor, walls, group, step);
        var a = window.document.createElement('a');
        a.href = window.URL.createObjectURL(new Blob([JSON.stringify(sceneJSON)], {type: 'text/dta'}));
        a.download = 'test.dta';
        a.target = '_blank';

        document.body.appendChild(a);
        a.click();

        document.body.removeChild(a);
        return sceneJSON;
    }

    /**
     * 添加地板
     */
    function addFloor(id) {
        if (!isEdit) {
            alert("当前非编辑模式");
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
            alert("当前非编辑模式");
            return;
        }

        $("#canvas2d").bind('mousedown', dragStart);
        wallid = id;
    }

    // 开始墙壁拖动事件
    var dragStart = function(e) {
        mouseX = e.pageX - e.currentTarget.offsetParent.offsetLeft - e.currentTarget.offsetLeft;
        mouseY = e.pageY - e.currentTarget.offsetParent.offsetTop - e.currentTarget.offsetTop;

        $("#canvas2d")
            .bind('mousemove', drag)
            .bind('mouseup', dragEnd);
    };

    // 墙壁拖动事件
    var drag = function(e) {
    };

    // 结束墙壁拖动事件
    var dragEnd = function(e) {
        var x = e.pageX - e.currentTarget.offsetParent.offsetLeft - e.currentTarget.offsetLeft;
        var y = e.pageY - e.currentTarget.offsetParent.offsetTop - e.currentTarget.offsetTop;

        var position = [];
        var rotation = 0;
        var size = [];
        console.log(x+" " +y);
        console.log(e);

        if (Math.abs(x - mouseX) < Math.abs(y - mouseY)) {
            x = mouseX;
            size = [Math.abs(y - mouseY), 0.1 * step];
            rotation = Math.PI / 2;
            position = [x, (y + mouseY) / 2];
//            position=[100,100];
        } else {
            y = mouseY;
            size = [Math.abs(x - mouseX), 0.1 * step];
            position = [(x + mouseX) / 2, y];
//            position=[100,100];
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
            alert("当前非编辑模式");
            return;
        }

        if (selected === undefined || selected.type !== CONST.TYPE.WALL) {
            alert("请选择一面墙壁");
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
    }

    function addSensor(id) {
        if (!isEdit) {
            alert("当前非编辑模式");
            return;
        }

        $("#canvas2d").unbind('mousedown', dragStart);
        var model = createFurniture(id, [width2d/2, height2d/2], 0);
        if (model !== null) {
            selectMode(model, CONST.TYPE.SENSOR);
            updateInfo(selected);
        }
    }

    /**
     * 添加家具模型
     * @param id
     */
    function addFurniture(id) {
        if (!isEdit) {
            alert("当前非编辑模式");
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
            case CONST.TYPE.SENSOR:
                if (isOut(selected.getBounds(), bounds)) {
                    selected.position.set(width2d / 2, height2d / 2);
                }
                break;
            default:
                alert("unknown model");
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
                case CONST.TYPE.SENSOR:
                    this.position.x = newPosition.x;
                    this.position.y = newPosition.y;
                    break;
                default:
                    alert("unknown model");
                    break;
            }
            updateInfo(selected);

        }
    }

    // 传感器信息
    function onMouseMove( event ) {
        //event.preventDefault();
        alert(event.target.id);
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
            alert("当前非编辑模式");
            return;
        }

        if (selected !== undefined) {
            switch (selected.type) {
                case CONST.TYPE.DOOR:
                case CONST.TYPE.WINDOW:
                    alert("请对门窗所在墙壁进行操作");
                    break;
                case CONST.TYPE.WALL:
                    $.each(selected.children, function (index, object) {
                        var offset = object.position.x - selected.position.x + object.position.y - selected.position.y;
                        object.rotation = (object.rotation + Math.PI / 2) % CONST.PI_2;
                        object.position.x = -Math.abs(Math.cos(object.rotation)) * offset + selected.position.x;
                        object.position.y = Math.abs(Math.sin(object.rotation)) * offset + selected.position.y;
                    });
                case CONST.TYPE.FURNITURE:
                case CONST.TYPE.SENSOR:
                    selected.rotation = (selected.rotation + Math.PI / 2) % CONST.PI_2;
                    updateInfo(selected);
                    break;
                default:
                    alert("unknown type");
                    break;
            }
        }
    }

    /**
     * 删除模型
     */
    function deleteModel() {
        if (!isEdit) {
            alert("当前非编辑模式");
            return;
        }

        if (selected !== undefined) {
            switch (selected.type) {
                case CONST.TYPE.DOOR:
                case CONST.TYPE.WINDOW:
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
                case CONST.TYPE.SENSOR:
                    group.removeChild(selected);
                    updateInfo();
                    break;
                default:
                    alert("unknown model");
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
            alert("当前非编辑模式");
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