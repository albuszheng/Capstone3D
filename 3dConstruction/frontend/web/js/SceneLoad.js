SceneLoad = function () {};

SceneLoad.prototype = {
    constructor: SceneLoad,

    load3d: function (data, width, height, canvas, models) {
        var clock = new THREE.Clock();
        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);

        var renderer = new THREE.WebGLRenderer();
        renderer.setClearColor(new THREE.Color(0xD1D1D1, 1.0));
        renderer.setSize(width, height);

        camera.position.x = 1;
        camera.position.y = 0.5;
        camera.position.z = 2;
        camera.lookAt(new THREE.Vector3(0, 0, 0));

        var controls = new THREE.FirstPersonControls(camera);
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

        canvas.innerHTML="";
        var firstPersonButton = document.createElement('button');
        firstPersonButton.addEventListener('click', function(){
            camera.position.x = 1;
            camera.position.y = 0.5;
            camera.position.z = 2;
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
        });
        var firstPesonText = document.createTextNode('第一人称视角');
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
                    model.position[0] - 10 + Math.sin(rotation) * 0.05,
                    0,
                    model.position[1] - 10 + Math.cos(rotation) * 0.05];
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
                    model.position[0] - 10 - Math.sin(rotation)*0.05,
                    2,
                    model.position[1] - 10 - Math.cos(rotation)*0.05];
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
                    model.position[0] - 10 + Math.sin(rotation) * 0.05,
                    2,
                    model.position[1] - 10 + Math.cos(rotation) * 0.05];
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
                var position3D = [object.position[0]-10, 0, object.position[1]-10];
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

    load2d: function (data, width, height, canvas, models) {
        var step = Math.min(width, height);

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
                        loadDoorWindow(object.sensors, wall, CONST.TYPE.SENSOR);
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

        return stage;
    },

    loadfloor: function (data, step, width, height, canvas, canEdit, building_id, floor_no) {
        var rooms = data.rooms;
        var modules = data.modules;
        var isEdit = false;
        var selected = undefined;

        canvas.innerHTML="";
        var renderer = PIXI.autoDetectRenderer(step * width, step * height, {'transparent': true});
        var stage = new PIXI.Container();

        var linegraph = new PIXI.Graphics();
        stage.addChildAt(linegraph, 0);
        createLine();

        var group = new PIXI.Container();
        stage.addChildAt(group, 1);

        if (canEdit) {
            // 保存
            var save = document.createElement('button');
            save.addEventListener('click', function(){
                viewMode();
                saveFloor();
            });
            var saveText = document.createTextNode('保存');
            save.appendChild(saveText);
            canvas.appendChild(save);

            // 编辑
            var edit = document.createElement('button');
            edit.addEventListener('click', function(){
                editMode();
            });
            var editText = document.createTextNode('编辑');
            edit.appendChild(editText);
            canvas.appendChild(edit);

            // 删除
            var deleteButton = document.createElement('button');
            deleteButton.addEventListener('click', function(){
                if (isEdit) {
                    if (selected !== undefined) {
                        group.removeChild(selected);
                    }
                } else {
                    alert('当前非编辑模式');
                }
            });
            var deleteText = document.createTextNode('删除');
            deleteButton.appendChild(deleteText);
            canvas.appendChild(deleteButton);

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
            change.appendChild(changeText);
            canvas.appendChild(change);

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
                canvas.appendChild(module);

            });
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

            $.ajax({
                type: 'post',
                data: {data: sceneJSON, id: building_id, floor: floor_no},
                url: 'index.php?r=site/update-floor',
                async: false,
                success: function (data) {
                    console.log(data.result);

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
            //graphics.lineStyle(2, 0x0000FF, 1);
            graphics.beginFill(0xFFFF0B, 0.5);
            graphics.drawRect(0, 0, size[0]*step, size[1]*step);
            graphics.endFill();
            room.addChildAt(graphics, 0);

            var roomText = new PIXI.Text(room_no, {});
            room.addChildAt(roomText, 1);

            group.addChild(room);
            return room;
        }

        function onMouseOver() {
            var new_style = {
                font : 'bold italic 28px Arial',
                fill : '#4cae4c'
            };
            this.getChildAt(1).style = new_style;

        }

        function onMouseOut() {
            this.getChildAt(1).style = {};
        }

        function onMouseClick() {
            var room_no = this.getChildAt(1).text;
            if (canEdit) {
                window.location.href = 'index.php?r=site/edit-room&room='+room_no+'&floor='+floor_no+'&building='+building_id;
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
    }
}