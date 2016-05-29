<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model model
 *
 * @property integer $id
 * @property string $name
 * @property string $size
 * @property string $scale
 * @property string $url2d
 * @property string $url3d
 * @property integer $type
 * @property string $param
 */
class Model extends ActiveRecord
{
    const TYPE_FLOOR = 0; //地板
    const TYPE_WALL = 1; //墙
    const TYPE_DOOR = 2; //门
    const TYPE_WINDOW = 3; //窗
    const TYPE_FURNITURE = 4; //家具
    const TYPE_SENSOR = 5; //传感器

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

    /**
     * Finds all models
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findAllModels()
    {
        $sql = 'select * from model';
        $models = parent::findBySql($sql)->all();
        return $models;
    }

    public static function deleteById($id) {
        $model = self::findById($id);
        return $model->delete();
    }

    public static function updateModel($data) {
        $model = self::findById($data['id']);
        $model->name = $data['name'];
        $model->size = $data['size'];
        $model->scale = $data['scale'];
        $model->url2d = $data['url2d'];
        $model->url3d = $data['url3d'];
        $model->type = $data['type'];
        return $model->save();
    }

    /**
     * Finds all sensor model
     *
     * @return static[]
     */
    public static function findAllSensors()
    {
        return static::findAll(['type' => self::TYPE_SENSOR]);
    }

}