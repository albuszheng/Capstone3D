<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model sensor
 *
 * @property integer $id
 * @property integer $model_id
 * @property string $param1
 * @property string $param2
 * @property integer $room_id
 * @property string $position
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
            [['id', 'model_id'], 'required'],
            [['id', 'model_id', 'room_id'], 'integer'],
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

    public static function updateSensor($id, $room_id, $position) {
        $sensor = self::findById($id);
        $sensor->room_id = $room_id;
        $sensor->position = $position;
        return $sensor->save();
    }

}