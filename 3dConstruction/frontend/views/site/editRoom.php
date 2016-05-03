<?php

/* @var $this yii\web\View */
/* @var $data string */
/* @var $room_id integer */

use yii\helpers\Html;
use frontend\assets\ThreeAsset;

ThreeAsset::register($this);

$this->title = 'Edit Room';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-view-room">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>This is one room:</p>

    <div id="webgl-output">
        <div id="2dbutton">
            <button onclick="save()">save</button>
            <button onclick="load()">load</button>
            <button onclick="edit()">edit</button>
            <button onclick="see()">see</button>
            <button onclick="to3d()">to3d</button>
        </div>

        <div id="3dbutton" style="visibility: hidden">
            <button id="to2dbutton">to2d</button>
        </div>

        <div>
            <button id="1" onclick="addFloor(this.id)">floor0</button>
            <button id="2" onclick="addFloor(this.id)">floor1</button>
            <button id="3" onclick="addFloor(this.id)">floor2</button>
            <button id="4" onclick="addWall(this.id)">wall</button>
            <button id="5" onclick="addDoorWindow(this.id, CONST.TYPE.DOOR)">door</button>
            <button id="6" onclick="addDoorWindow(this.id, CONST.TYPE.WINDOW)">window</button>
            <button id="7" onclick="addFurniture(this.id)">bed</button>
            <button id="8" onclick="addFurniture(this.id)">cabinet</button>
            <button id="9" onclick="addFurniture(this.id)">drawer</button>
            <button id="12" onclick="addFurniture(this.id)">sofa</button>
            <button id="11" onclick="addFurniture(this.id)">table</button>
            <button id="10" onclick="addFurniture(this.id)">TV</button>
        </div>
        <div>
            <button onclick="rotateModel()">rotate</button>
            <button onclick="deleteModel()">delete</button>
            <button onclick="clearModel()">clear</button>
        </div>
        <div id="info">
            <ul>
                <li id="pos">position:</li>
                <li id="rot">rotation:</li>
            </ul>
        </div>

        <div id="canvas">
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

    var width = $('#canvas').width();
    var height = $('#canvas').height();
    var pos = document.getElementById("pos");
    var rot = document.getElementById("rot");
    var step = Math.min(width, height);
    var models = [];
    var data = null;

    var stage, floor, walls, group, graph;
    var selected = undefined;
    var isEdit = false;

    function start() {
        if (<?php echo $room_id ?> !== null) {
            if (<?php echo $data ?> !== null) {
                data = <?php echo $data ?>;
            }

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
            if (data !== null) {
                if (data.type === "scene") {
                    load(data);
                    console.log('load');
                }
            } else {
                $.getJSON('scene/init.json', function(result) {
                    data = result;
                    load(data);
                });
            }

        } else {
            document.getElementById('webgl-output').innerHTML='No Room';
        }
    }

    // 网格线
    function createLine() {
        graph = new PIXI.Graphics();
        graph.lineStyle(1, 0x000, 1);
        for (var i = step; i < width; i+=step) {
            graph.moveTo(i, 0);
            graph.lineTo(i, width);

            graph.moveTo(0, i);
            graph.lineTo(width, i);
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
            data:{id:<?php echo $room_id ?>, data:JSON.stringify(sceneJSON)},
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
    function load(scene) {
        if (isEdit) {
            if(confirm("是否保存当前场景?")) {
                save();
            }
        }

        if (scene === undefined) {
            scene = data;
        }

        var loader = new SceneLoad();
        stage = loader.load2d(scene, width, height, document.getElementById('canvas'), models);
        floor = stage.getChildAt(0);
        walls = stage.getChildAt(1);
        group = stage.getChildAt(2);

        step = Math.min(width/scene.floor.width, height/scene.floor.height);
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
        $("#canvas").unbind('mousedown', dragStart);

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
        document.getElementById('to2dbutton').onclick = function() {
          to2d(sceneJSON);
        };

        var loader = new SceneLoad();
        loader.load3d(sceneJSON, width, height, document.getElementById('canvas'), models);
    }

    /**
     * 查看2d场景
     */
    function to2d(data) {
        $('#2dbutton').css('visibility', 'visible');
        $('#3dbutton').css('visibility', 'hidden');

        var loader = new SceneLoad();
        stage = loader.load2d(data, width, height, document.getElementById('canvas'), models);
        floor = stage.getChildAt(0);
        walls = stage.getChildAt(1);
        group = stage.getChildAt(2);

        step = Math.min(width/data.floor.width, height/data.floor.height);
        createLine();
    }

    /**
     * 添加地板
     */
    function addFloor(id) {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        $("#canvas").unbind('mousedown', dragStart);
        floor.texture = PIXI.Texture.EMPTY;

        var model = findModelById(id);
        if (model !== null) {
            var url = model.url2d;
            if (url !== "null") {
                floor.texture = PIXI.Texture.fromImage('model/images/' + url);
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
        $("#canvas").unbind('mousedown', dragStart);

        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        $("#canvas").bind('mousedown', dragStart);
        wallid = id;
    }

    // 开始墙壁拖动事件
    var dragStart = function(e) {
        mouseX = e.pageX - e.currentTarget.offsetLeft;
        mouseY = e.pageY - e.currentTarget.offsetTop;

        $("#canvas")
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

        var wall = createWall(wallid, position, rotation, size);
        selectMode(wall, CONST.TYPE.WALL);
        updateInfo(selected);

        $("#canvas")
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

        $("#canvas").unbind('mousedown', dragStart);
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

    /**
     * 添加家具模型
     * @param id
     * @param size
     */
    function addFurniture(id) {
        if (!isEdit) {
            console.log("当前非编辑模式");
            return;
        }

        $("#canvas").unbind('mousedown', dragStart);
        var model = createFurniture(id, [width/2, height/2], 0);
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

        var bounds = new PIXI.Rectangle(0, 0, width, height);
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
                        object.position.x = Math.abs(Math.cos(object.rotation)) * offset + width / 2;
                        object.position.y = Math.abs(Math.sin(object.rotation)) * offset + height / 2;
                    });
                    selected.position.set(width / 2, height / 2);
                }
                break;
            case CONST.TYPE.FURNITURE:
                if (isOut(selected.getBounds(), bounds)) {
                    selected.position.set(width / 2, height / 2);
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
            var texture = PIXI.Texture.fromImage('model/images/' + model.url2d);
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