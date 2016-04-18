<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model model
 *
 * @property integer $id
 * @property string $size
 * @property string $scale
 * @property string $url2d
 * @property string $url3d
 */
class Model extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%model}}';
    }

    public function rules()
    {
        return [
            [['id', 'size', 'url2d'], 'required'],
            ['url2d', 'image', 'extensions' => 'png, jpg'],
        ];
    }

    /**
     * Finds model by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

}