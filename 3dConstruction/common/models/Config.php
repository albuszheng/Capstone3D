<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model config
 *
 * @property integer $floor
 */
class Config extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%config}}';
    }

    public function rules()
    {
        return [
            ['floor', 'integer'],
            ['floor', 'required'],
        ];
    }

    /**
     * Get floor
     *
     * @return array|null|ActiveRecord
     */
    public static function getFloor()
    {
        return parent::find()->one();
    }

    /**
     * Update floor
     *
     * @param $floor
     * @return bool
     */
    public static function updateFloor($floor) {
        $config = parent::find()->one();
        $config->floor = $floor;
        return $config->save();
    }

}