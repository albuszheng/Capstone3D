<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model room
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $data
 * @property integer $last_modify_id
 * @property string $last_modify_time
 */
class Room extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%room}}';
    }

    public function rules()
    {
        return [
            ['id', 'unique'],
            [['id', 'user_id'], 'integer'],
            [['id', 'user_id'], 'required'],
            ['last_modify_time', 'datetime'],
        ];
    }

    /**
     * Finds room by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds rooms by user_id
     *
     * @param string $user_id
     * @return static|null
     */
    public static function findByUserId($user_id)
    {
        return static::findAll(['user_id' => $user_id]);
    }

    /**
     * room and user 通过 User.id -> user_id 关联建立一对一关系
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}