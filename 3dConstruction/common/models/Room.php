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
        return static::findOne(['user_id' => $user_id]);
    }

    /**
     * room and user 通过 User.id -> user_id 关联建立一对一关系
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Get room data
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Update room data by id
     * @param $id
     * @param $data
     * @return bool
     */
    public static function updateRoom($id, $data) {
        $room = self::findById($id);
        $room->data = $data;
        $room->last_modify_id = Yii::$app->getUser()->id;
        $room->last_modify_time = date('Y-m-d H:i:s');
        return $room->save();
    }

    /**
     * Assgin a room to a user
     *
     * @param $id
     * @param $user_id
     * @return bool
     */
    public static function registerRoom($id, $user_id) {
        $room = self::findById($id);
        $room->user_id = $user_id;
        return $room->save();
    }

    public static function unregisterRoom($id) {
        $room = self::findById($id);
        $room->user_id = null;
        return $room->save();
    }

    /**
     * Whether room is registered
     *
     * @param $id
     * @return bool
     */
    public static function isRegisteredRoom($id) {
        $room = self::findById($id);
        return isset($room->user_id);
    }
}