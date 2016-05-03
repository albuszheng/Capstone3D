<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

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

    /**
     * Finds orders by user_id
     *
     * @param $user_id
     * @return array
     */
    public static function findByUserId($user_id)
    {
//        $sql = 'select * from order o,order_detail od where o.user_id='+$user_id+' and o.id=od.order_id';
        $query = (new Query())
            ->select([
                'o.time as time',
                'o.price as price',
                'o.staff_id as staff_id',
                'goods.name as name',
                'goods.price as unit_price',
                'od.number as number'
            ])
            ->from(['order o', 'order_detail od', 'goods'])
            ->where([
                'o.user_id' => $user_id,
                'o.id' => 'od.oder_id',
                'goods.id' => 'od.goods_id',
            ])
            ->createCommand();
        return $query->queryAll();
    }

}