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
    DEVIATION: 0.00001

    ///**
    // * Constant to identify the Model Type.
    // *
    // * @static
    // * @constant
    // * @property {object} TYPE
    // * @property {number} TYPE.FLOOR
    // * @property {number} TYPE.WALL
    // * @property {number} TYPE.DOOR
    // * @property {number} TYPE.WINDOW
    // * @property {number} TYPE.FURNITURE
    // */
    //TYPE: {
    //    FLOOR:     0,
    //    WALL:      1,
    //    DOOR:      2,
    //    WINDOW:    3,
    //    FURNITURE: 4
    //}
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
                '           "size": ['+ wall.width/scale + ',' + wall.height/scale + '],',
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
            return "[" + v.x / scale + "," + v.y / scale + "]";

        }

        var output = [
            '{',
            '   "version": "' + CONST.VERSION + '",',
            '   "type": "scene",',

            '   "floor": {',
            '       "type": "floor",',
            '       "width": 20' + ',',
            '       "height": 20' + ',',
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

        console.log(output);
        return JSON.parse(output);

    }
}
