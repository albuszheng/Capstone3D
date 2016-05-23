<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model building
 *
 * @property integer $id
 * @property integer $building_no
 * @property integer $floor
 * @property integer $x_axis
 * @property integer $y_axis
 * @property integer $width
 * @property integer $height
 */
class Building extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%building}}';
    }

    public function rules()
    {
        return [
            ['id', 'unique'],
            [['id', 'building_no', 'floor', 'x_axis', 'y_axis', 'width', 'height'], 'integer'],
            [['building_no', 'floor', 'x_axis', 'y_axis', 'width', 'height'], 'required'],
        ];
    }

    /**
     * Finds building by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds all buildings
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findAllBuildings()
    {
        $sql = 'select * from building';
        $building = parent::findBySql($sql)->all();
        return $building;
    }

}