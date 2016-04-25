/**
 * Created by liujia on 16/4/24.
 */

// 创建地板
function createFloor(id, floor) {
    var model = findModelById(id);

    if (model !== null) {
        var url = model.url2d;
        if (url !== "null") {
            floor.texture = PIXI.Texture.fromImage('model/images/' + url);
        }
        floor.id = model.id;
    }
}

// 创建墙壁
function createWall(id, position, rotation, size, walls) {
    var model = findModelById(id);
    var wall = null;

    if (model !== null) {
        var texture = PIXI.Texture.fromImage('model/images/' + model.url2d);
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

// 创建门窗
function createDoorWindow(id, position, rotation, walls) {
    var model = null;
    var doorwindow = findModelById(id);

    if (doorwindow !== null) {
        var texture = PIXI.Texture.fromImage('model/plan/' + doorwindow.url2d);
        model = new PIXI.Sprite(texture);
        model.interactive = false;
        model.buttonMode = false;
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
function createFurniture(id, position, rotation, group) {
    var furniture = findModelById(id);
    var model = null;

    if (furniture !== null) {
        var texture = PIXI.Texture.fromImage('model/plan/' + furniture.url2d);
        model = new PIXI.Sprite(texture);
        model.interactive = false;
        model.buttonMode = false;
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
