<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model floor
 *
 * @property integer $id
 * @property integer $floor_no
 * @property integer $building_id
 * @property string $data
 * @property integer $last_modify_id
 * @property string $last_modify_time
 */
class Floor extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%floor}}';
    }

    public function rules()
    {
        return [
            ['id', 'unique'],
            [['id', 'floor_no', 'building_id'], 'integer'],
            [['id', 'floor_no'], 'required'],
        ];
    }

    /**
     * Finds floor by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

}