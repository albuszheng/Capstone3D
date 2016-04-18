<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model goods
 *
 * @property integer $id
 * @property double $price
 * @property string $name
 */
class Goods extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%goods}}';
    }

    public function rules()
    {
        return [
            ['id', 'unique'],
            ['id', 'integer'],
            ['id', 'required'],
            ['price', 'double', 'min' => 0.00],
        ];
    }

    /**
     * Finds goods by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

}