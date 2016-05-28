/**
 * Created by liujia on 16/4/1.
 */

/**
 * Constant values
 */
var CONST = {
    /**
     * String of the current version
     *
     * @static
     * @constant
     * @property {string} VERSION
     */
    VERSION: '1.0.0',

    /**
     * @static
     * @constant
     * @property {number} PI_2 - Two Pi
     */
    PI_2: Math.PI * 2,

    /**
     * @static
     * @constant
     * @property {number} DEVIATION
     */
    DEVIATION: 0.00001,

    /**
     * Constant to identify the Model Type.
     *
     * @static
     * @constant
     * @property {object} TYPE
     * @property {number} TYPE.FLOOR
     * @property {number} TYPE.WALL
     * @property {number} TYPE.DOOR
     * @property {number} TYPE.WINDOW
     * @property {number} TYPE.FURNITURE
     * @property {number} TYPE.SENSOR
     */
    TYPE: {
        FLOOR:     0,
        WALL:      1,
        DOOR:      2,
        WINDOW:    3,
        FURNITURE: 4,
        SENSOR:    5
    }
};


SceneExport = function () {};

SceneExport.prototype = {
    constructor: SceneExport,

    parse: function ( floor, walls, group, scale ) {

        var wall = [];
        var objects = [];

        for (var i = 0; i < walls.children.length; i++) {
            var object = walls.getChildAt(i);
            if (object instanceof PIXI.Sprite && object.type === CONST.TYPE.WALL) {
                wall.push("\n" + WallString(object));
            }
        }

        for (var i = 0; i < group.children.length; i++) {
            var object = group.getChildAt(i);
            if (object instanceof PIXI.Sprite) {
                objects.push("\n" + FurnitureString(object));
            }
        }

        /**
         * 将墙壁类模型转换String格式,方便保存为JSON
         * @param wall
         * @returns {string}
         * @constructor
         */
        function WallString(wall) {
            var doors = [];
            var windows = [];

            for (var i = 0; i < wall.children.length; i++) {
                var object = wall.getChildAt(i);
                if (object instanceof PIXI.Sprite) {
                    if (object.type === CONST.TYPE.DOOR) {
                        doors.push("\n" + DoorWindowString(object, "door"));
                    }

                    if (object.type === CONST.TYPE.WINDOW) {
                        windows.push("\n" + DoorWindowString(object, "window"));
                    }
                }
            }

            var output = [
                '       {',
                '           "type": "wall",',
                '           "id": "' + wall.id + '",',
                '           "size": ['+ (wall.width/scale).toFixed(2) + ',' + (wall.height/scale).toFixed(2) + '],',
                '           "position": ' + Vector2String(wall.position, scale) + ',',
                '           "rotation": ' + wall.rotation/Math.PI + ',',
                '           "doors": [',
                            doors,
                '           ],',
                '           "windows": [',
                            windows,
                '           ]',
                '       }'
            ].join( '\n' );

            return output;
        }

        /**
         * 将门窗类模型转换String格式,方便保存为JSON
         * @param model
         * @returns {string}
         * @constructor
         */
        function DoorWindowString(model, type) {
            var output = [
                '              {',
                '                 "type": "' + type + '",',
                '                 "id": "' + model.id + '",',
                '                 "position": ' + Vector2String(model.position, scale) + ',',
                '                 "rotation": ' + model.rotation/Math.PI,
                '              }'
            ].join( '\n' );

            return output;
        }

        /**
         * 将家具类模型转换String格式,方便保存为JSON
         * @param furniture
         * @returns {string}
         * @constructor
         */
        function FurnitureString(furniture) {
            var output = [
                '       {',
                '           "type": "furniture",',
                '           "id": "' + furniture.id + '",',
                '           "position": ' + Vector2String(furniture.position, scale) + ',',
                '           "rotation": ' + furniture.rotation/Math.PI,
                '       }'
            ].join( '\n' );

            return output;
        }

        function Vector2String( v, sca ) {
            var scale = sca || 1;
            return "[" + (v.x / scale).toFixed(2) + "," + (v.y / scale).toFixed(2) + "]";

        }

        var output = [
            '{',
            '   "version": "' + CONST.VERSION + '",',
            '   "type": "scene",',

            '   "floor": {',
            '       "type": "floor",',
            '       "width": ' + floor._width / scale + ',',
            '       "height": ' + floor._height / scale + ',',
            '       "id": "' + floor.id + '"',
            '   },',
            '',

            '   "wall": [',
            wall,
            '   ],',
            '',

            '   "objects": [',
            objects,
            '   ]',
            '}'
        ].join( '\n' );

        return JSON.parse(output);

    },

    parseInitRoom: function ( width, height ) {
        var wall = [];

        wall.push("\n" + WallString((width-0.1), width/2, 0.1, 0));
        wall.push("\n" + WallString((height-0.1), (width-0.1), height/2, 0.5));
        wall.push("\n" + WallString((width-0.1), width/2, (height-0.1), 1));
        wall.push("\n" + WallString((height-0.1), 0.1, height/2, 1.5));

        /**
         * 将墙壁类模型转换String格式,方便保存为JSON
         * @param wall
         * @returns {string}
         * @constructor
         */
        function WallString(width, x, y, rotation) {
            var output = [
                '       {',
                '           "type": "wall",',
                '           "id": "4",',
                '           "size": ['+ (width-0.1).toFixed(2) + ',' + 0.1 + '],',
                '           "position": [' + x.toFixed(2) + ',' + y.toFixed(2) + "],",
                '           "rotation": ' + rotation + ',',
                '           "doors": [',
                '           ],',
                '           "windows": [',
                '           ]',
                '       }'
            ].join( '\n' );

            return output;
        }

        var output = [
            '{',
            '   "version": "' + CONST.VERSION + '",',
            '   "type": "scene",',

            '   "floor": {',
            '       "type": "floor",',
            '       "width": ' + width + ',',
            '       "height": ' + height + ',',
            '       "id": "1"',
            '   },',
            '',

            '   "wall": [',
            wall,
            '   ],',
            '',

            '   "objects": [',
            '   ]',
            '}'
        ].join( '\n' );

        return JSON.parse(output);
    },

    parseFloor: function ( group, width, height, scale, isExport ) {
        var rooms = [];

        if (isExport) {
            for (var i = 0; i < group.length; i++) {
                var room = group[i];
                rooms.push("\n" + ExportString(room));
            }
        } else {
            for (var i = 0; i < group.length; i++) {
                var room = group[i];
                if (room instanceof PIXI.Container) {
                    rooms.push("\n" + RoomString(room));
                }
            }
        }


        /**
         * 将房间转换String格式,方便保存为JSON
         * @param room
         * @returns {string}
         * @constructor
         */
        function RoomString(room) {
            var output = [
                '       {',
                '           "id": "' + room.id + '",',
                '           "room_no": "' + room.getChildAt(1).text + '",',
                '           "size": "' + room._width/scale + "," + room._height/scale + '",',
                '           "position": "' + Vector2String(room.position.x, room.position.y, scale) + '"',
                '       }'
            ].join( '\n' );

            return output;
        }

        function ExportString(room) {
            var output = [
                '       {',
                '           "room_no": "' + room.room_no + '",',
                '           "size": "' + room.size + '",',
                '           "position": "' + room.position + '",',
                '           "data": ' + room.data,
                '       }'
            ].join( '\n' );

            return output;
        }

        function Vector2String( x, y, sca ) {
            var scale = sca || 1;
            return (x / scale).toFixed(2) + "," + (y / scale).toFixed(2);

        }

        var output = [
            '{',
            '   "version": "' + CONST.VERSION + '",',
            '   "type": "floor",',
            '   "width": ' + width + ',',
            '   "height": ' + height + ',',
            '   "room": [',
            rooms,
            '   ]',
            '}'
        ].join( '\n' );

        return output;
    },

    parseBuilding: function ( group ) {
        var buildings = [];

        for (var i = 0; i < group.length; i++) {
            var building = group[i];
            if (building instanceof THREE.Mesh) {
                buildings.push("\n" + BuildingString(building));
            }
        }

        /**
         * 将建筑转换String格式,方便保存为JSON
         *
         * @param building
         * @returns {string}
         * @constructor
         */
        function BuildingString(building) {
            var output = [
                '       {',
                '           "building_no": "' + building.building_no + '",',
                '           "floor": "' + building.floor + '",',
                '           "x_axis": "' + building.x_axis + '",',
                '           "y_axis": "' + building.y_axis + '",',
                '           "width": "' + building.floor_width + '",',
                '           "height": "' + building.floor_height + '"',
                '       }'
            ].join( '\n' );

            return output;
        }

        var output = [
            '{',
            '   "version": "' + CONST.VERSION + '",',
            '   "type": "building",',
            '   "buildings": [',
            buildings,
            '   ]',
            '}'
        ].join( '\n' );

        //return JSON.parse(output);
        return output;
    },

    parseBuildingRomms: function ( data, width, height, floor ) {
        var rooms = [];

        for (var i = 0; i < data.length; i++) {
            var room = data[i];
            rooms.push("\n" + ExportString(room));
        }

        function ExportString(room) {
            var data = !(room.data) ? 'null' : room.data;
            var output = [
                '       {',
                '           "floor_no": "' + room.floor_no + '",',
                '           "room_no": "' + room.room_no + '",',
                '           "size": "' + room.size + '",',
                '           "position": "' + room.position + '",',
                '           "data": ' + data,
                '       }'
            ].join('\n');

            return output;
        }


        var output = [
            '{',
            '   "version": "' + CONST.VERSION + '",',
            '   "type": "building",',
            '   "floor": ' + floor + ',',
            '   "width": ' + width + ',',
            '   "height": ' + height + ',',
            '   "room": [',
            rooms,
            '   ]',
            '}'
        ].join('\n');

        return JSON.parse(output);
    }
}
