<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model operation
 *
 * @property integer $id
 * @property string $operation
 */
class Operation extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%operation}}';
    }

    public function rules()
    {
        return [
            ['id', 'unique'],
            ['id', 'integer'],
            [['id', 'operation'], 'required'],
        ];
    }

    /**
     * Finds operation by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

}