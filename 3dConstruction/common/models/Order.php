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
 * @property double $price
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
            [['id', 'price'], 'required'],
            ['price', 'double', 'min' => 0.0],
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