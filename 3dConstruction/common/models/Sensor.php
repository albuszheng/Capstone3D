<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model sensor
 *
 * @property integer $id
 * @property integer $model_id
 * @property integer $room_id
 * @property string $position
 * @property float $rotation
 * @property string $data
 */
class Sensor extends ActiveRecord
{

    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%sensor}}';
    }

    public function rules()
    {
        return [
            ['model_id', 'required'],
            [['id', 'model_id', 'room_id'], 'integer'],
        ];
    }

    /**
     * Finds sensor by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds all sensors
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findAllSensors()
    {
        $sql = 'select * from sensor';
        $sensors = parent::findBySql($sql)->all();
        return $sensors;
    }

    /**
     * Finds sensors by room_id
     *
     * @param $room_id
     * @return static[]
     */
    public static function findSensorsByRoom($room_id)
    {
        return static::findAll(['room_id' => $room_id]);
    }

    public static function updateSensor($id, $room_id, $position, $rotation) {
        $sensor = self::findById($id);
        $sensor->room_id = $room_id;
        $sensor->position = $position;
        $sensor->rotation = $rotation;
        return $sensor->save() ? $sensor : null;
    }

    public static function deleteById($id) {
        $sensor = self::findById($id);
        return $sensor->delete();
    }

}