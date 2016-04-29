<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model authority log
 *
 * @property integer $id
 * @property integer $operator_id
 * @property integer $user_id
 * @property integer $operation_id
 * @property string $time
 */
class AuthorityLog extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%authority_log}}';
    }

    public function rules()
    {
        return [
            ['id', 'unique'],
            [['id', 'operator_id', 'user_id', 'operation_id'], 'integer'],
        ];
    }

    /**
     * Finds log by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

    public function getOperation($operation_id)
    {
        return $this->hasOne(Operation::className(), ['id' => $operation_id]);
    }

}