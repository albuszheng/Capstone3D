<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model order
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $staff_id
 * @property string $goods
 * @property double $amount
 * @property string $time
 */
class Order extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    public function rules()
    {
        return [
            ['id', 'unique'],
            [['id', 'user_id', 'staff_id'], 'integer'],
            [['id', 'user_id', 'staff_id', 'goods'], 'required'],
            ['amount', 'double', 'min' => 0.0],
            ['time', 'datetime'],
        ];
    }

    /**
     * Finds order by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

}