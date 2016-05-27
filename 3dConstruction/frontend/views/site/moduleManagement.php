<?php

/* @var $this yii\web\View */
/* @var $modules \yii\db\ActiveRecord[] */

use yii\helpers\Html;

$this->title = 'Manage Module';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-manage-module">

    <div class="modal fade template-canvas" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">

        <div class="modal-dialog modal-lg">

            <div class="modal-title"></div>
            
                <div class="modal-content canvas" style="width: 640px">
                    <div id="2d-btn" class="btn-group"></div>

                    <div id="3d-btn" class="btn-group" role="group" style="visibility: hidden"></div>

                    <div id="models-btn" class="btn-group" role="group"></div>
                    <div id="operate-btn" class="btn-group" role="group"></div>
                </div>
                <div class="modal-content canvas" id="canvas2d"></div>
                <div class="modal-content canvas" id="canvas3d" style="width: 640px;"></div>

<!--            <div class="modal-content canvas" id="module-canvas">-->
<!--            </div>-->
        </div>
    </div>

    <div class="container">
        <h2 class="title">模版管理</h2>
        <div class="template-list">
            <table class="table table-hover">
                <thead>
                <tr>
                    <td>#</td>
                    <td>模版名称</td>
                    <td>尺寸</td>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($modules as $module): ?>
                    <tr>
                        <td><?= $module->id?></td>
                        <td><button class="btn btn-link template-name" id=<?= $module->id?> data-toggle="modal" data-target=".template-canvas" data-content=<?= $module->data?> data-name="<?= $module->name?>" data-size="<?= $module->size?>"><?= $module->name?></button></td>
                        <td><?= $module->size?></td>
                    </tr>
                <?php endforeach;?>

                </tbody>
            </table>
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
<script type="text/javascript" src="js/bootstrap.js"></script>

<script>
    $(".template-canvas").on('show.bs.modal', function (event) {
        var width2d = $('#canvas2d').width();
        var width3d = $('#canvas3d').width();
        var height2d = $('#canvas2d').height();
        var height3d = $('#canvas3d').height();
        var canvas = document.getElementById('canvas2d');
        var step = Math.min(width2d, height2d);
        var models = [];

        var selected = undefined;
        var isEdit = false;
        var graph;

        var button = $(event.relatedTarget);
        var data = button.data('content');
        var module_id = button.context.id;

        var renderer = PIXI.autoDetectRenderer(width2d, height2d, {'transparent': true});
        canvas.innerHTML="";
        canvas.appendChild(renderer.view);

        var stage = new PIXI.Container();

        var floor = new PIXI.Sprite(PIXI.Texture.EMPTY);
        floor.width = width2d;
        floor.height = height2d;
        floor.interactive = false;
        floor.id = 1;
        stage.addChildAt(floor, 0);

        var walls = new PIXI.Container();
        stage.addChildAt(walls, 1);

        var group = new PIXI.Container();
        stage.addChildAt(group, 2);

        animate();
        start();

        var modal = $(this);
        modal.find('.modal-title').text(button.data('content'));

        function animate() {
            requestAnimationFrame(animate);
            renderer.render(stage);
        }

        function start() {
            $.ajax({
                type:'post',
                data:{},
                url:'index.php?r=/site/find-all-models',
                async : false,
                success: function (data) {
                    models = data.models;
                },

                error:function(xhr) {
                    console.log(xhr.responseText);
                }

            });

            var isLoad = false;
            if (data) {
                if (data.type === "scene") {
                    isLoad = true;
                }
            } else {
                var room_size = button.data('size');
                var size = room_size.split(',');
                var exporter = new SceneExport();
                var sceneJSON = exporter.parseInitRoom(size[0], size[1]);
                data = sceneJSON;
                isLoad = true;
            }

            if (isLoad) {
                step = Math.min(width2d/data.floor.width, height2d/data.floor.height);
                createLine();
//                load(data);
                to2d(data);
            }

            // 2d操作button
            var button2d = document.getElementById('2d-btn');
            button2d.innerHTML="";
            var save_btn = createModelButton(button2d, '保存');
            save_btn.addEventListener('click', function() {save();}, false);
            var load_btn = createModelButton(button2d, '加载');
            load_btn.addEventListener('click', function() {load();}, false);
            var edit_btn = createModelButton(button2d, '编辑');
            edit_btn.addEventListener('click', function() {edit();}, false);
            var see_btn = createModelButton(button2d, '查看');
            see_btn.addEventListener('click', function() {see();}, false);
            var see3d_btn = createModelButton(button2d, '查看3D场景');
            see3d_btn.addEventListener('click', function() {to3d();}, false);
            see3d_btn.id = 'to3dbutton';

            // 3d操作button
            var button3d = document.getElementById('3d-btn');
            button3d.innerHTML="";
            var see2d_btn = createModelButton(button3d, '查看2D场景');
            see2d_btn.id = 'to2dbutton';
            see2d_btn.addEventListener('click', function() {to2d();}, false);

            // 模型button
            var models_btn = document.getElementById('models-btn');
            models_btn.innerHTML="";
            for (var i = 0; i < models.length; i++) {
                var button = createModelButton(models_btn, models[i].name);
                button.id = models[i].id;

                switch (models[i].type) {
                    case CONST.TYPE.FLOOR:
                        button.addEventListener('click', function() {addFloor(this.id);}, false);
                        break;
                    case CONST.TYPE.WALL:
                        button.addEventListener('click', function() {addWall(this.id);}, false);
                        break;
                    case CONST.TYPE.DOOR:
                        button.addEventListener('click', function() {addDoorWindow(this.id, CONST.TYPE.DOOR)}, false);
                        break;
                    case CONST.TYPE.WINDOW:
                        button.addEventListener('click', function() {addDoorWindow(this.id, CONST.TYPE.WINDOW)}, false);
                        break;
                    case CONST.TYPE.FURNITURE:
                        button.addEventListener('click', function() {addFurniture(this.id)}, false);
                        break;
                    case CONST.TYPE.SENSOR:
                        button.addEventListener('click', function() {addSensor(this.id)}, false);
                        break;
                    default:
                        alert('unknown type');
                        break;
                }
            }

            // 模型操作button
            var operate_btns = document.getElementById('operate-btn');
            operate_btns.innerHTML="";
            var rotate_btn = createModelButton(operate_btns, '旋转');
            rotate_btn.addEventListener('click', function() {rotateModel();}, false);
            var del_btn = createModelButton(operate_btns, '删除');
            del_btn.addEventListener('click', function() {deleteModel();}, false);
            var clear_btn = createModelButton(operate_btns, '清空');
            clear_btn.addEventListener('click', function() {clearModel();}, false);
        }

        function createModelButton(element, name) {
            var model = document.createElement('button');
            var modelText = document.createTextNode(name);
            
            model.appendChild(modelText);
            element.appendChild(model);
            model.setAttribute("class","btn btn-small btn-link");
            return model;
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
                data:{id:module_id, data:JSON.stringify(sceneJSON)},
                url:'index.php?r=site/update-module',
                success:function(result) {
                    if (result.result === true) {
                        data = sceneJSON;
                        see();
                    } else {
                    }

                },

                error:function(xhr) {
                    console.log(xhr.responseText);
                }

            });
        }

        /**
         * 加载场景
         */
        function load(scene) {
            if (scene === undefined) {
                scene = data;
            }

            if (isEdit) {
                if(confirm("是否保存当前场景?")) {
                    save();
                }
            }

            walls.removeChildren(0, walls.children.length);
            group.removeChildren(0, group.children.length);

            loadFloor(scene.floor);
            loadWall(scene.wall);
            loadFurniture(scene.objects);
            see();
        }

        // 加载地板
        function loadFloor(data) {
            var model = findModelById(data.id);

            if (model !== null) {
                var url = model.url2d;
                if (url !== "null") {
                    floor.texture = PIXI.Texture.fromImage('model/images/floor/' + url);
                }
                floor.id = model.id;
            }
        }

        // 加载墙壁
        function loadWall(data) {
            $.each(data, function (index, object) {
                var position = [object.position[0] * step, object.position[1] * step];
                var rotation = object.rotation * Math.PI;
                var size = [object.size[0] * step, object.size[1] * step];
                var wall = createWall(object.id, position, rotation, size);

                if (wall !== null) {
                    if (object.doors !== undefined) {
                        loadDoorWindow(object.doors, wall, CONST.TYPE.DOOR);
                    }

                    if (object.windows !== undefined) {
                        loadDoorWindow(object.windows, wall, CONST.TYPE.WINDOW);
                    }

//                    if (object.sensors !== undefined) {
//                        loadSensor(object.sensors, wall, CONST.TYPE.SENSOR);
//                    }
                }
            });
        }

        // 加载门窗
        function loadDoorWindow(data, wall, type) {
            $.each(data, function (index, object) {
                var position = [object.position[0] * step, object.position[1] * step];
                var rotation = object.rotation * Math.PI;

                var model = createDoorWindow(object.id, position, rotation);
                if (model !== null) {
                    model.type = type;
                    model.wall = wall;
                    wall.children.push(model);
                }
            });
        }

   /*     // 加载传感器
        function loadSensor(data, wall, type) {
            $.each(data, function (index, object) {
                var position = [object.position[0] * step, object.position[1] * step];
                var rotation = object.rotation * Math.PI;

                var model = createSensor(object.id, position, rotation);
                if (model !== null) {
                    model.type = type;
                    model.wall = wall;
                    wall.children.push(model);
                }
            });
        }*/

        // 加载家具模型
        function loadFurniture(data) {
            $.each(data, function (index, object) {
                var position = [object.position[0] * step, object.position[1] * step];
                createFurniture(object.id, position, object.rotation);

            });
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

            $('#2d-btn').css('visibility', 'hidden');
            $('#3d-btn').css('visibility', 'visible');
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
            $('#2d-btn').css('visibility', 'visible');
            $('#3d-btn').css('visibility', 'hidden');
            $('#canvas3d').css('display', 'none');
            $('#canvas2d').css('display', 'block');

//            var loader = new SceneLoad();
//            stage = loader.load2d(data, width2d, height2d, document.getElementById('canvas2d'), models);
//            floor = stage.getChildAt(0);
//            walls = stage.getChildAt(1);
//            group = stage.getChildAt(2);
//
//            step = Math.min(width2d/data.floor.width, height2d/data.floor.height);
//            createLine();
            load(data);

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
//            updateInfo(selected);

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
            }
        }

        // 开始模型拖动事件
        function onDragStart(event) {
            selectMode(this, event.target.type);
            this.data = event.data;
            this.dragging = true;
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
            }
        }

        // 传感器信息
        function onMouseMove( event ) {
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
                model.type = furniture.type;
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

        // 创建传感器
        function createSensor(id, position, rotation) {
            var model = null;
            var sensor = findModelById(id);

            if (sensor !== null) {
                var texture = PIXI.Texture.fromImage('model/plan/' + sensor.url2d);
                model = new PIXI.Sprite(texture);
                model.anchor.set(0, 0.5);
                model.position.set(position[0], position[1]);
                var size = sensor.size.split(',');
                model.width = size[0] * step;
                model.height = size[2] * step * 2;
                model.rotation = rotation;
                model.id = id;
                model.info = 'info'+position[0];
                model.interactive = true;
                model.buttonMode = true;

                model
                    .on('mousedown', onMouseMove);

                walls.addChild(model);
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
                        break;
                    case CONST.TYPE.FURNITURE:
                    case CONST.TYPE.SENSOR:
                        group.removeChild(selected);
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

    })
</script>
