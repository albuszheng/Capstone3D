<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model order
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $goods_id
 * @property integer $number
 */
class OrderDetail extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%order_detail}}';
    }

    public function rules()
    {
        return [
            ['id', 'unique'],
            [['id', 'order_id', 'goods_id'], 'integer'],
            [['id', 'number'], 'required'],
            ['number', 'integer', 'min' => 1],
        ];
    }

    /**
     * Finds order detail by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }


    /**
     * Finds order detail by order_id
     * @param $order_id
     * @return static[]
     */
    public static function findByOrderId($order_id)
    {
        return static::findAll(['order_id' => $order_id]);
    }

}