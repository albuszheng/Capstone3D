SceneLoad = function () {};

SceneLoad.prototype = {
    constructor: SceneLoad,

    load3d: function (data, width, height, canvas, models) {
        var clock = new THREE.Clock();
        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 500);

        var renderer = new THREE.WebGLRenderer();
        renderer.setClearColor(new THREE.Color(0xD1D1D1, 1.0));
        renderer.setSize(width, height);

        camera.position.x = 1;
        camera.position.y = 1;
        camera.position.z = 2;
        camera.lookAt(new THREE.Vector3(0, 0, 0));

        var floor_width = data.floor.width;
        var floor_height = data.floor.height;
        var controls = new THREE.FirstPersonControls(camera, document, floor_width/2, floor_height/2);
        controls.lookSpeed = 0.1;
        controls.movementSpeed = 5;
        controls.noFly = true;
        controls.lookVertical = false;
        //controls.constrainVertical = true;
        //controls.verticalMin = 1.0;
        //controls.verticalMax = 2.0;
        controls.lon = -120;
        controls.lat = 0;

        var ambientLight = new THREE.AmbientLight( 0xffffff );
        scene.add( ambientLight );
        var directionalLight = new THREE.DirectionalLight( 0xffffff );
        directionalLight.position.set( 0, 0, 0 ).normalize();
        scene.add( directionalLight );

        canvas.innerHTML="";
        var firstPersonButton = document.createElement('button');
        firstPersonButton.addEventListener('click', function(){
            camera.position.x = 1;
            camera.position.y = 0.5;
            camera.position.z = 2;
            controls = new THREE.FirstPersonControls(camera, document, floor_width/2, floor_height/2);
            controls.lookSpeed = 0.1;
            controls.movementSpeed = 5;
            controls.noFly = true;
            controls.lookVertical = true;
            controls.constrainVertical = true;
            controls.verticalMin = 1.0;
            controls.verticalMax = 2.0;
            controls.lon = -180;
            controls.lat = 0;
        });
        var firstPesonText = document.createTextNode('第一人称视角');
        firstPersonButton.setAttribute("class", "btn btn-default btn-sm");
        firstPersonButton.appendChild(firstPesonText);
        canvas.appendChild(firstPersonButton);

        var overButton = document.createElement('button');
        overButton.addEventListener('click', function(){
            camera.position.x = 0;
            camera.position.y = 16;
            camera.position.z = 20;
            controls = new THREE.TrackballControls(camera);
            controls.rotateSpeed = 1.0;
            controls.zoomSpeed = 1.0;
            controls.panSpeed = 1.0;
        });
        var overText = document.createTextNode('总览视角');
        overButton.appendChild(overText);
        overButton.setAttribute("class", "btn btn-default btn-sm");
        canvas.appendChild(overButton);

        canvas.appendChild(renderer.domElement);

        load(data);
        render();

        function load(data) {
            if (data.type === "scene") {
                loadFloor(data.floor);
                loadWall(data.wall);
                loadObject(data.objects);
            }
        }

        function render() {
            controls.update(clock.getDelta());
            renderer.clear();
            requestAnimationFrame(render);
            renderer.render(scene, camera);
        }

        // 加载地板
        function loadFloor(data) {
            // plane
            var planeGeometry = new THREE.PlaneGeometry( data.width, data.height, 0, 0 );
            var material = new THREE.MeshBasicMaterial();

            var floor = findModelById(data.id);
            if (floor !== null) {
                var url = floor.url2d;
                if (url !== 'null') {
                    var texture = new THREE.TextureLoader().load('model/images/floor/' + url);
                    material.map = texture;
                }

                material.side = THREE.DoubleSide;
                var plane = new THREE.Mesh( planeGeometry, material );
                plane.rotateX(-Math.PI/2);
                scene.add( plane );
            }

        }

        // 加载墙壁
        function loadWall(data) {
            $.each(data, function (index, object) {
                var wall = findModelById(object.id);
                if (wall !== null ){
                    var group = new THREE.Object3D();
                    var url = 'model/images/wall/' + wall.url2d;
                    var wallTexture = new THREE.TextureLoader().load(url);
                    var wallMaterial = new THREE.MeshBasicMaterial({map: wallTexture});
                    wallMaterial.side = THREE.DoubleSide;

                    var size = [object.size[0], 4, object.size[1]];
                    var position = [object.position[0]-floor_width/2, 2, object.position[1]-floor_height/2];
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

                    if (object.sensors !== undefined) {
                        $.each(object.sensors, function(index, model) {
                            wallBSP = loadSensor(model, wallBSP, rotation, group);
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
                }

            });

        }

        // 加载门
        function loadDoor(model, wallBSP, wallrotation, group) {
            var door = findModelById(model.id);

            if (door !== null) {
                var rotation = -model.rotation * Math.PI;
                var position3D = [
                    model.position[0] - floor_width/2 + Math.sin(rotation) * 0.05,
                    0,
                    model.position[1] - floor_height/2 + Math.cos(rotation) * 0.05];
                var rotation3D = [-Math.PI/2 , 0, rotation];
                var size = door.size.split(',');
                var bspposition = convertBSPPosition(rotation3D[2], position3D, size);
                loadModel(model.id, position3D, rotation3D, group);

                var modelBSP = new ThreeBSP(addWall(size, bspposition, wallrotation));
                wallBSP = wallBSP.subtract(modelBSP);
            }

            return wallBSP;

        }

        // 加载窗
        function loadWindow(model, wallBSP, wallrotation, group) {
            var window = findModelById(model.id);

            if (window !== null) {
                var defaultRotation = 0.5 * Math.PI;
                var rotation = -model.rotation * Math.PI;
                var position3D = [
                    model.position[0] - floor_width/2 - Math.sin(rotation)*0.05,
                    2,
                    model.position[1] - floor_height/2 - Math.cos(rotation)*0.05];
                var rotation3D = [-Math.PI/2 , 0, rotation-defaultRotation];
                var size = window.size.split(',');
                var tmpsize = size.slice();
                if (isZero(Math.cos(defaultRotation))) {
                    tmpsize[0] = size[2];
                    tmpsize[2] = size[0];
                }
                var bspposition = convertBSPPosition(rotation3D[2], position3D, tmpsize);
                loadModel(model.id, position3D, rotation3D, group);

                var modelBSP = new ThreeBSP(addWall(size, bspposition, wallrotation));
                wallBSP = wallBSP.subtract(modelBSP);
            }

            return wallBSP;
        }

        // 加载传感器
        function loadSensor(model, wallBSP, wallrotation, group) {
            var sensor = findModelById(model.id);

            if (sensor !== null) {
                var rotation = -model.rotation * Math.PI;
                var position3D = [
                    model.position[0] - floor_width/2 + Math.sin(rotation) * 0.05,
                    2,
                    model.position[1] - floor_height/2 + Math.cos(rotation) * 0.05];
                var rotation3D = [-Math.PI/2 , 0, rotation];
                var size = sensor.size.split(',');
                //var bspposition = convertBSPPosition(rotation3D[2], position3D, size);
                loadModel(model.id, position3D, rotation3D, group);

                //var modelBSP = new ThreeBSP(addWall(size, bspposition, wallrotation));
                //wallBSP = wallBSP.subtract(modelBSP);
            }

            return wallBSP;

        }

        // 加载家具模型
        function loadObject(data) {
            $.each(data, function (index, object) {
                var position3D = [object.position[0]-floor_width/2, 0, object.position[1]-floor_height/2];
                var rotation3D = [-Math.PI/2 , 0, -object.rotation * Math.PI];

                loadModel(object.id, position3D, rotation3D, scene);
            });
        }

        // 加载模型
        function loadModel(id, position, rotation, scene) {
            var model = findModelById(id);
            if (model !== null) {
                var loader = new THREE.ColladaLoader();
                loader.load(
                    'model/' + model.url3d,
                    function ( collada ) {
                        var voxel = collada.scene;
                        voxel.rotation.fromArray(rotation);
                        voxel.position.fromArray(position);
                        voxel.scale.fromArray(model.scale.split(','));
                        voxel.id = id;
                        scene.add( voxel );
                    }
                );
            }

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

        //根据id查找模型信息
        function findModelById(id) {
            for (var i = 0; i < models.length; i++) {
                if (id == models[i].id) {
                    return models[i];
                }
            }
            return null;
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

    },

    load2d: function (data, width, height, canvas, models, step) {
        var step = step || Math.min(width, height);

        var renderer = PIXI.autoDetectRenderer(width, height, {'transparent': true});
        canvas.innerHTML="";
        canvas.appendChild(renderer.view);

        var stage = new PIXI.Container();

        var floor = new PIXI.Sprite(PIXI.Texture.EMPTY);
        floor.width = width;
        floor.height = height;
        floor.interactive = false;
        floor.id = 1;
        stage.addChildAt(floor, 0);

        var walls = new PIXI.Container();
        stage.addChildAt(walls, 1);

        var group = new PIXI.Container();
        stage.addChildAt(group, 2);

        animate();
        load(data);

        function animate() {
            requestAnimationFrame(animate);
            renderer.render(stage);
        }

        // 加载场景
        function load(data) {
            step = Math.min(width/data.floor.width, height/data.floor.height);

            walls.removeChildren(0, walls.children.length);
            group.removeChildren(0, group.children.length);

            loadFloor(data.floor);
            loadWall(data.wall);
            loadFurniture(data.objects);

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

                    if (object.sensors !== undefined) {
                        loadSensor(object.sensors, wall, CONST.TYPE.SENSOR);
                    }
                }
            });
        }

        // 创建墙壁
        function createWall(id, position, rotation, size) {
            var model = findModelById(id);
            var wall = null;

            if (model !== null) {
                var texture = PIXI.Texture.fromImage('model/images/wall/' + model.url2d);
                wall = new PIXI.Sprite(texture);
                wall.anchor.set(0.5, 0.5);
                wall.position.set(position[0], position[1]);
                wall.rotation = rotation;
                wall.width = size[0];
                wall.height = size[1];
                wall.type = CONST.TYPE.WALL;
                wall.id = id;
                wall.interactive = false;
                wall.buttonMode = false;

                wall
                    .on('mousedown', onDragStart)
                    .on('mouseup', onDragEnd)
                    .on('mouseupoutside', onDragEnd)
                    .on('mousemove', onDragMove);

                walls.addChild(wall);
            }

            return wall;
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

        // 创建门窗
        function createDoorWindow(id, position, rotation) {
            var model = null;
            var doorwindow = findModelById(id);

            if (doorwindow !== null) {
                var texture = PIXI.Texture.fromImage('model/plan/' + doorwindow.url2d);
                model = new PIXI.Sprite(texture);
                model.anchor.set(0, 0.5);
                model.position.set(position[0], position[1]);
                var size = doorwindow.size.split(',');
                model.width = size[0] * step;
                model.height = size[2] * step * 2;
                model.rotation = rotation;
                model.id = id;
                model.interactive = false;
                model.buttonMode = false;

                model
                    .on('mousedown', onDragStart)
                    .on('mouseup', onDragEnd)
                    .on('mouseupoutside', onDragEnd)
                    .on('mousemove', onDragMove);

                walls.addChild(model);
            }

            return model;
        }

        // 加载传感器
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

        // 加载家具模型
        function loadFurniture(data) {
            $.each(data, function (index, object) {
                var position = [object.position[0] * step, object.position[1] * step];
                createFurniture(object.id, position, object.rotation);

            });
        }

        // 创建家具模型
        function createFurniture(id, position, rotation) {
            var furniture = findModelById(id);

            if (furniture !== null) {
                var texture = PIXI.Texture.fromImage('model/plan/' + furniture.url2d);
                var model = new PIXI.Sprite(texture);
                model.anchor.set(0, 1);
                model.position.set(position[0], position[1]);
                model.rotation = rotation * Math.PI;
                var size = furniture.size.split(',');
                model.width = size[0] * step;
                model.height = size[1] * step;
                model.type = CONST.TYPE.FURNITURE;
                model.id = id;
                model.interactive = false;
                model.buttonMode = false;

                model
                    .on('mousedown', onDragStart)
                    .on('mouseup', onDragEnd)
                    .on('mouseupoutside', onDragEnd)
                    .on('mousemove', onDragMove);

                group.addChild(model);
            }
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

        // 传感器信息
        function onMouseMove( event ) {
            //event.preventDefault();
            alert(event.target.info);
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
                        alert("unknown model");
                        break;
                }
                updateInfo(selected);

            }
        }

        return stage;
    },

    loadfloor: function (data, step, width, height, canvas, btnGroup, canEdit, building_id, floor_no) {
        var rooms = data.rooms;
        var modules = data.modules;
        var isEdit = false;
        var selected = undefined;
        var delIds = [];

        canvas.innerHTML="";
        var renderer = PIXI.autoDetectRenderer(step * width, step * height, {'transparent': true});
        var stage = new PIXI.Container();

        var linegraph = new PIXI.Graphics();
        stage.addChildAt(linegraph, 0);
        createLine();

        var group = new PIXI.Container();
        stage.addChildAt(group, 1);

        if (canEdit) {
            // var btn_group = document.getElementById("button-group");
            // 保存
            var save = document.createElement('button');
            save.addEventListener('click', function(){
                saveFloor();
                viewMode();
            });
            var saveText = document.createTextNode('保存');
            save.appendChild(saveText);
            save.setAttribute('class', 'btn btn-default btn-sm');
            btnGroup.appendChild(save);

            // 编辑
            var edit = document.createElement('button');
            edit.addEventListener('click', function(){
                editMode();
            });
            var editText = document.createTextNode('编辑');
            edit.appendChild(editText);
            edit.setAttribute('class', 'btn btn-default btn-sm');
            btnGroup.appendChild(edit);

            // 删除
            var deleteButton = document.createElement('button');
            deleteButton.addEventListener('click', function(){
                if (isEdit) {
                    if (selected !== undefined) {
                        if (selected.id !== undefined) {
                            delIds.push(selected.id);
                        }
                        group.removeChild(selected);
                    }
                } else {
                    alert('当前非编辑模式');
                }
            });
            var deleteText = document.createTextNode('删除');
            deleteButton.setAttribute('class', 'btn btn-default btn-sm');
            deleteButton.appendChild(deleteText);
            btnGroup.appendChild(deleteButton);

            // 更改房间号
            var change = document.createElement('button');
            change.addEventListener('click', function(){
                if (isEdit) {
                    if (selected !== undefined) {
                        var roomText = selected.getChildAt(1);
                        var room_no = prompt("请输入房间号:", roomText.text);
                        if (room_no != null){
                            roomText.text = room_no;
                        }
                    }
                } else {
                    alert('当前非编辑模式');
                }
            });
            var changeText = document.createTextNode('更改房间号');
            change.setAttribute('class', 'btn btn-default btn-sm');
            change.appendChild(changeText);
            btnGroup.appendChild(change);

            $.each(modules, function (index, object) {
                var module = document.createElement('button');
                module.addEventListener('click', function(){
                    if (isEdit) {
                        var room_no = prompt("请输入房间号:","101");
                        if (room_no != null){
                            var size = object.size.split(',');
                            var room = createRoom(size, [width/2,height/2], room_no);
                            room
                                .on('mousedown', onDragStart)
                                .on('mouseup', onDragEnd)
                                .on('mouseupoutside', onDragEnd)
                                .on('mousemove', onDragMove);
                            selectMode(room);
                        }
                    } else {
                        alert('当前非编辑模式');
                    }

                });
                var moduleText = document.createTextNode(object.name);
                module.appendChild(moduleText);
                module.setAttribute('class', 'btn btn-default btn-sm');
                btnGroup.appendChild(module);

            });

            var importInput = document.createElement('input');
            importInput.type = 'file';
            importInput.id = 'importFile';
            canvas.appendChild(importInput);

            // 导入
            var importButton = document.createElement('button');
            importButton.addEventListener('click', function(){
                importFloor();
            });
            var importText = document.createTextNode('导入');
            importButton.appendChild(importText);
            canvas.appendChild(importButton);
            importButton.setAttribute('class', 'btn btn-default');
            importButton.style.display = 'none';

            // 导出
            var exportButton = document.createElement('button');
            exportButton.addEventListener('click', function(){
                exportFloor();
            });
            var exportText = document.createTextNode('导出');
            exportButton.setAttribute('class', 'btn btn-default');
            exportButton.appendChild(exportText);
            canvas.appendChild(exportButton);
            exportButton.style.display = 'block';

        }

        canvas.appendChild(renderer.view);
        canvas.style.border = "none";
        renderer.view.style.border = "1px solid gray";

        animate();
        load(rooms);

        function animate() {
            requestAnimationFrame(animate);
            renderer.render(stage);
        }

        // 网格线
        function createLine() {
            var step_10 = step * 10;
            linegraph.lineStyle(1, 0x000, 1);
            for (var i = step_10; i < width*step; i+=step_10) {
                linegraph.moveTo(i, 0);
                linegraph.lineTo(i, height*step);
            }

            for (var i = step_10; i < height*step; i+=step_10) {
                linegraph.moveTo(0, i);
                linegraph.lineTo(width*step, i);
            }
            linegraph.visible = false;
        }

        // 加载楼层场景
        function load(rooms) {
            group.removeChildren(0, group.children.length);

            $.each(rooms, function (index, object) {
                var size = object.size.split(',');
                var position = object.position.split(',');
                var room = createRoom(size, position, object.room_no || 0);
                room.id = object.id;
                room
                    .on('mouseover', onMouseOver)
                    .on('mouseout', onMouseOut)
                    .on('click', onMouseClick);

            });
        }

        // 保存楼层场景
        function saveFloor() {
            var exporter = new SceneExport();
            var sceneJSON = exporter.parseFloor(group.children, width, height, step);
            if (delIds.length == 0) {
                delIds.push(0);
            }

            $.ajax({
                type: 'post',
                data: {data: sceneJSON, dels: delIds, id: building_id, floor: floor_no},
                url: 'index.php?r=site/update-floor',
                async: false,
                success: function (data) {
                    if (data.result == true) {
                        var ids = data.ids;
                        for (var i = 0; i < group.children.length; i++) {
                            group.getChildAt(i).id = ids[i];
                        }
                    }

                },

                error: function (xhr) {
                    console.log(xhr.responseText);
                }

            });
        }

        // 导入楼层场景
        function importFloor() {
            if (!isEdit) {
                alert("当前非编辑状态!");
            }

            if (typeof FileReader) {
                var file = document.getElementById('importFile').files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.readAsText(file, 'utf-8');
                    reader.onload = function (e) {
                        $.ajax({
                            type: 'post',
                            data: {data:JSON.parse(this.result), id: building_id, floor: floor_no},
                            url: 'index.php?r=site/import-floor',
                            success: function (data) {
                                if (data.result) {
                                    load(data.rooms);
                                }
                            },

                            error: function (xhr) {
                                console.log(xhr.responseText);
                            }

                        });

                        viewMode();
                    }
                } else {
                    alert("请选择规范的文件导入!");
                }


            } else {
                alert("您的浏览器不支持此功能!");
            }

        }

        // 导出楼层场景
        function exportFloor() {
            if (isEdit) {
                alert("请先保存场景!");
            }

            $.ajax({
                type: 'post',
                data: {id: building_id, floor: floor_no},
                url: 'index.php?r=site/export-floor',
                success: function (data) {
                    var exporter = new SceneExport();
                    var sceneJSON = exporter.parseFloor(data.rooms, width, height, step, true);
                    //alert(JSON.parse(sceneJSON));
                    var a = window.document.createElement('a');
                    a.href = window.URL.createObjectURL(new Blob([sceneJSON], {type: 'text/dta'}));
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

        function createRoom(size, position, room_no) {
            var room = new PIXI.Container();
            room.position.set(position[0]*step, position[1]*step);
            room._width = size[0]*step;
            room._height = size[1]*step;
            room.interactive = true;
            room.buttonMode = true;

            var graphics = new PIXI.Graphics();
            //graphics.lineStyle(4, 0x99CCFF, 1);
            graphics.beginFill(0xB2F7C4, 1);
            graphics.drawRect(0, 0, size[0]*step, size[1]*step);
            //graphics.drawRect(2, 2, size[0]*step-4, size[1]*step-4);
            graphics.endFill();
            room.addChildAt(graphics, 0);

            var roomText = new PIXI.Text(room_no, {fill: '#1BE634'});
            room.addChildAt(roomText, 1);

            group.addChild(room);
            return room;
        }

        function onMouseOver() {
            var new_style = {
                font : 'bold italic 28px Arial',
                fill : '#26E6A8'
            };
            this.getChildAt(1).style = new_style;

        }

        function onMouseOut() {
            this.getChildAt(1).style = {fill: '#1BE634'};
        }

        function onMouseClick() {
            if (canEdit) {
                window.location.href = 'index.php?r=site/edit-room&room_id='+this.id;
            } else {
                window.location.href = 'index.php?r=site/view-room&room_id='+this.id;
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

            var bounds = new PIXI.Rectangle(0, 0, width*step, height*step);
            if (isOut(selected.getBounds(), bounds)) {
                selected.position.set(width * step / 2, height * step / 2);
            }
        }

        // 模型拖动事件
        function onDragMove() {
            if (this.dragging) {
                var newPosition = this.data.getLocalPosition(this.parent);
                this.position.x = newPosition.x;
                this.position.y = newPosition.y;
            }
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
        function selectMode(model) {
            if (selected !== undefined) {
                selected.alpha = 1;

            }
            selected = model;
            selected.alpha = 0.5;
        }

        function editMode() {
            isEdit = true;
            linegraph.visible = true;
            importButton.style.display = 'block';
            exportButton.style.display = 'none';
            $.each(group.children, function (index, object) {
                object.removeAllListeners();
                object
                    .on('mousedown', onDragStart)
                    .on('mouseup', onDragEnd)
                    .on('mouseupoutside', onDragEnd)
                    .on('mousemove', onDragMove);
            });
        }

        function viewMode() {
            isEdit = false;
            linegraph.visible = false;
            importButton.style.display = 'none';
            exportButton.style.display = 'block';
            if (selected !== undefined) {
                selected.alpha = 1;
                selected = undefined;
            }
            $.each(group.children, function (index, object) {
                object.removeAllListeners();
                object
                    .on('mouseover', onMouseOver)
                    .on('mouseout', onMouseOut)
                    .on('click', onMouseClick);
            });
        }

        return stage;
    },

    loadOverview: function(data, width, height, canvas, canEdit) {
        var isShiftDown = false;
        var objects = [];
        var buildings = [];
        var addBuildings = [];
        var mouse = new THREE.Vector2(), INTERSECTED;

        var scene = new THREE.Scene();

        var camera = new THREE.PerspectiveCamera(45, width / height, 1, 10000);
        camera.position.set(0, 700, 1400);
        camera.lookAt(new THREE.Vector3());

        // roll-over helpers
        var rollOverGeo = new THREE.BoxGeometry(100, 200, 100);
        var rollOverMaterial = new THREE.MeshBasicMaterial({color: 0x66CCCC, opacity: 0.6, transparent: true});
        var rollOverMesh = new THREE.Mesh(rollOverGeo, rollOverMaterial);
        rollOverMesh.visible = false;
        scene.add(rollOverMesh);

        // cubes
        var cubeGeo = new THREE.BoxGeometry(100, 200, 100);
        var cubeTexture = new THREE.TextureLoader().load( "img/building2.png" );
        var matArray = [];
        var mapMaterial = new THREE.MeshBasicMaterial({map:cubeTexture});
        mapMaterial.map.wrapS = THREE.RepeatWrapping;
        mapMaterial.map.wrapT = THREE.RepeatWrapping;
        mapMaterial.map.repeat.set(1,2);
        var roofMaterial = new THREE.MeshBasicMaterial({map:new THREE.TextureLoader().load( "img/roof3.png" )});
        matArray.push(mapMaterial);
        matArray.push(mapMaterial);
        matArray.push(roofMaterial);
        matArray.push(mapMaterial);
        matArray.push(mapMaterial);
        matArray.push(new THREE.MeshBasicMaterial({}));
        var cubeMaterial = new THREE.MeshFaceMaterial(matArray);


        // grid
        var size = 500, step = 100;
        var geometry = new THREE.Geometry();
        for (var i = -size; i <= size; i += step) {
            geometry.vertices.push(new THREE.Vector3(-size, 0, i));
            geometry.vertices.push(new THREE.Vector3(size, 0, i));
            geometry.vertices.push(new THREE.Vector3(i, 0, -size));
            geometry.vertices.push(new THREE.Vector3(i, 0, size));
        }

        var material = new THREE.LineBasicMaterial({color: 0x000000, opacity: 0.6, transparent: true});
        var line = new THREE.LineSegments(geometry, material);
        line.visible = false;
        scene.add(line);

        var raycaster = new THREE.Raycaster();

        var geometry = new THREE.Geometry();
        //var geometry = new THREE.PlaneBufferGeometry(1000,1000);
        //geometry.rotateX(-Math.PI / 2);

        var planMaterial1 = new THREE.MeshBasicMaterial({color: 0x82E676});
        var planMaterial2 = new THREE.MeshBasicMaterial({color: 0xCCEAC4});
        for (var i = 0; i < 10; i++) {
            for (var j = (i+1) % 2; j < 10; j = j + 2) {
                var planGeometry = new THREE.PlaneGeometry(97, 97);
                planGeometry.rotateX(-Math.PI / 2);
                var planMesh = new THREE.Mesh(planGeometry, planMaterial1);
                planMesh.position.set(i * 100 - 450, 0, j * 100 - 450);
                THREE.GeometryUtils.merge(geometry, planMesh, 0);

            }

            for (var j = i % 2; j < 10; j = j + 2) {
                var planGeometry2 = new THREE.PlaneGeometry(97, 97);
                planGeometry2.rotateX(-Math.PI / 2);
                var planMesh2 = new THREE.Mesh(planGeometry2, planMaterial2);
                planMesh2.position.set(i * 100 - 450, 0, j * 100 - 450);
                THREE.GeometryUtils.merge(geometry, planMesh2, 1);
            }
        }


        var plane = new THREE.Mesh(geometry, new THREE.MeshFaceMaterial([planMaterial1, planMaterial2]));
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

        canvas.innerHTML="";
        if (canEdit) {
            // 保存
            var save = document.createElement('button');
            save.addEventListener('click', function () {
                viewMode();
                saveBuilding();
            });
            save.setAttribute('class', 'btn btn-default btn-sm');
            var saveText = document.createTextNode('保存');
            save.appendChild(saveText);
            canvas.appendChild(save);

            // 编辑
            var edit = document.createElement('button');
            edit.setAttribute('class', 'btn btn-default btn-sm');
            edit.addEventListener('click', function () {
                editMode();
            });
            var editText = document.createTextNode('编辑');
            edit.appendChild(editText);
            canvas.appendChild(edit);

            // 退出
            var exit = document.createElement('button');
            exit.setAttribute('class', 'btn btn-default btn-sm');
            exit.addEventListener('click', function () {
                if (isEdit) {
                    if(confirm("是否保存当前场景?")) {
                        saveBuilding();
                    } else {
                        for (var i = addBuildings.length; i > 0; i--) {
                            scene.remove(addBuildings[i-1]);
                            objects.splice(objects.indexOf(addBuildings[i-1]), 1);
                            buildings.splice(objects.indexOf(addBuildings[i-1]), 1);
                            addBuildings.pop();
                        }
                    }
                    viewMode();
                }
            });
            var exitText = document.createTextNode('退出');
            exit.appendChild(exitText);
            canvas.appendChild(exit);
        }

        canvas.appendChild(renderer.domElement);

        load(data);
        viewMode();

        function load(data) {
            $.each(data, function(index, object) {
                var voxel = new THREE.Mesh(cubeGeo, cubeMaterial);
                voxel.position.set(object.x_axis*100-550, 100, object.y_axis*100-550);
                voxel.building_id = object.id;
                voxel.building_no = object.building_no;
                voxel.floor = object.floor;
                voxel.x_axis = object.x_axis;
                voxel.y_axis = object.y_axis;
                voxel.floor_width = object.width;
                voxel.floor_height = object.height;

                scene.add(voxel);
                objects.push(voxel);
                buildings.push(voxel);
            });
            requestAnimationFrame(render);
        }

        // 保存建筑场景
        function saveBuilding() {
            if (addBuildings.length > 0) {
                var exporter = new SceneExport();
                var sceneJSON = exporter.parseBuilding(addBuildings);

                $.ajax({
                    type: 'post',
                    data: {data: sceneJSON},
                    url: 'index.php?r=site/add-buildings',
                    async: false,
                    success: function (data) {
                        if (data.result == true) {
                            var ids = data.ids;
                            for (var i = 0; i < addBuildings.length; i++) {
                                addBuildings[i].building_id = ids[i];
                            }
                        }
                    },

                    error: function (xhr) {
                        console.log(xhr.responseText);
                    }

                });
            }
        }

        function onMouseMove(event) {
            event.preventDefault();
            mouse.x = ( (event.pageX - event.target.offsetLeft) / width ) * 2 - 1;
            mouse.y = - ( (event.pageY - event.target.offsetTop) / height ) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);
            var intersects = raycaster.intersectObjects(objects);

            if (intersects.length > 0) {
                var intersect = intersects[0];
                rollOverMesh.position.copy(intersect.point).add(intersect.face.normal);
                rollOverMesh.position.divideScalar(100).floor().multiplyScalar(100).addScalar(50);
                rollOverMesh.position.y = 100;
            }

            render();
        }

        function onMouseDown(event) {
            event.preventDefault();

            mouse.x = ( (event.pageX - event.target.offsetLeft) / width ) * 2 - 1;
            mouse.y = -( (event.pageY - event.target.offsetTop) / height ) * 2 + 1;

            raycaster = new THREE.Raycaster();
            raycaster.setFromCamera(mouse, camera);
            var intersects = raycaster.intersectObjects(objects);

            if (intersects.length > 0) {
                var intersect = intersects[0];

                // delete cube
                if (isShiftDown) {
                    //if (intersect.object != plane) {
                    //    scene.remove(intersect.object);
                    //    objects.splice(objects.indexOf(intersect.object), 1);
                    //    buildings.splice(objects.indexOf(intersect.object), 1);
                    //}

                // create cube
                } else {
                    var building_no = prompt("请输入建筑标号:", "1");
                    var floor = prompt("请输入楼层数:", "1");
                    var floor_width = prompt("请输入楼层长度:", "200");
                    var floor_height = prompt("请输入楼层宽度:", "80");
                    if ((building_no !== null) && (floor !== null) && (floor_width !== null) && (floor_height !== null)) {
                        var voxel = new THREE.Mesh(cubeGeo, cubeMaterial);
                        voxel.position.copy(intersect.point).add(intersect.face.normal);
                        voxel.position.divideScalar(100).floor().multiplyScalar(100).addScalar(50);
                        voxel.position.y = 100;
                        voxel.building_no = building_no;
                        voxel.floor = floor;
                        voxel.x_axis = (voxel.position.x + 550) / step;
                        voxel.y_axis = (voxel.position.z + 550) / step;
                        voxel.floor_width = floor_width;
                        voxel.floor_height = floor_height;
                        scene.add(voxel);
                        objects.push(voxel);
                        buildings.push(voxel);
                        addBuildings.push(voxel);
                    }
                }
                render();

            }
        }

        function onKeyDown(event) {
            switch (event.keyCode) {
                case 16:
                    isShiftDown = true;
                    break;
            }
        }

        function onKeyUp(event) {
            switch (event.keyCode) {
                case 16:
                    isShiftDown = false;
                    break;
            }
        }

        function render() {
            renderer.render(scene, camera);
        }

        function editMode() {
            isEdit = true;
            line.visible = true;
            rollOverMesh.visible = true;

            renderer.domElement.removeEventListener('mousemove', onMouseOver, false);
            renderer.domElement.removeEventListener('mousedown', onMouseClick, false);

            renderer.domElement.addEventListener('mousemove', onMouseMove, false);
            renderer.domElement.addEventListener('mousedown', onMouseDown, false);
            //document.addEventListener('keydown', onKeyDown, false);
            //document.addEventListener('keyup', onKeyUp, false);
            render();
        }

        function viewMode() {
            isEdit = false;
            line.visible = false;
            rollOverMesh.visible = false;

            renderer.domElement.removeEventListener('mousemove', onMouseMove, false);
            renderer.domElement.removeEventListener('mousedown', onMouseDown, false);
            //document.removeEventListener('keydown', onKeyDown, false);
            //document.removeEventListener('keyup', onKeyUp, false);

            renderer.domElement.addEventListener('mousemove', onMouseOver, false);
            renderer.domElement.addEventListener('mousedown', onMouseClick, false);
            render();
        }
        function onMouseOver( event ) {
            event.preventDefault();
            mouse.x = ( (event.pageX - event.currentTarget.offsetLeft) / width ) * 2 - 1;
            mouse.y = - ( (event.pageY - event.currentTarget.offsetTop) / height ) * 2 + 1;

            raycaster.setFromCamera( mouse, camera );
            var intersects = raycaster.intersectObjects( buildings );
            if ( intersects.length > 0 ) {
                if ( INTERSECTED !== intersects[ 0 ].object ) {
                    //if ( INTERSECTED ) INTERSECTED.material.emissive.setHex( INTERSECTED.currentHex );
                    INTERSECTED = intersects[ 0 ].object;
                    rollOverMesh.position.copy( INTERSECTED.position );
                    rollOverMesh.visible = true;
                }
            } else {
                if ( INTERSECTED ) rollOverMesh.visible = false;
                INTERSECTED = null;
            }

            requestAnimationFrame(render);
        }

        function onMouseClick(event) {
            event.preventDefault();
            if (INTERSECTED !== null) {
                window.location.href = 'index.php?r=site/view-building&id='+INTERSECTED.building_id;
            }
        }
    }
}