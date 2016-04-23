<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model model
 *
 * @property integer $id
 * @property string $size
 * @property string $scale
 * @property string $url2d
 * @property string $url3d
 * @property integer $type
 */
class Model extends ActiveRecord
{
    const TYPE_FLOOR = 0; //地板
    const TYPE_WALL = 1; //墙
    const TYPE_DOOR = 2; //门
    const TYPE_WINDOW = 3; //窗
    const TYPE_FURNITURE = 4; //家具

    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%model}}';
    }

    public function rules()
    {
        return [
            [['id', 'url2d'], 'required'],
            ['url2d', 'image', 'extensions' => 'png, jpg'],
        ];
    }

    /**
     * Finds model by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

}