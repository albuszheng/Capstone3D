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

    /**
     * Finds all goods
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findAllGoods()
    {
        $sql = 'select * from goods';
        $goods = parent::findBySql($sql)->all();
        return $goods;
    }

    /**
     * Finds name by id
     *
     * @param $id
     * @return string
     */
    public static function findNameById($id) {
        $goods = self::findById($id);
        return $goods->name;
    }

    /**
     * Update price by id
     *
     * @param $id
     * @param $name
     * @param $price
     * @return bool
     */
    public static function updateGoods($id, $name, $price) {
        $goods = self::findById($id);
        $goods->name = $name;
        $goods->price = $price;
        return $goods->save();
    }

    /**
     * Delete goods by id
     *
     * @param $id
     * @return false|int
     * @throws \Exception
     */
    public static function deleteGoods($id) {
        $goods = self::findById($id);
        return $goods->delete();
    }

}