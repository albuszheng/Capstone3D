<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model operation
 *
 * @property integer $id
 * @property string $operation
 * @property integer $user_group
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
            [['id', 'user_group'], 'integer'],
            [['id', 'operation', 'user_group'], 'required'],
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

    /**
     * Finds all operations
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findAllOperations()
    {
        $sql = 'select * from operation';
        $operations = parent::findBySql($sql)->all();
        return $operations;
    }

}