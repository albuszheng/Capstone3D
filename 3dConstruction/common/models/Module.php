<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model goods
 *
 * @property integer $id
 * @property string $name
 * @property string $size
 * @property string $data
 */
class Module extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%module}}';
    }

    public function rules()
    {
        return [
            ['id', 'unique'],
            ['id', 'integer'],
            ['id', 'required'],
        ];
    }

    /**
     * Finds module by id
     *
     * @param string $id
     * @return static|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds module by size
     *
     * @param $size
     * @return static[]
     */
    public static function findBySize($size)
    {
        return static::findAll(['size' => $size]);
    }

    /**
     * Finds all modules
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findAllModules()
    {
        $sql = 'select * from module';
        $module = parent::findBySql($sql)->all();
        return $module;
    }

    /**
     * Update by id
     *
     * @param $id
     * @param $data
     * @return bool
     */
    public static function updatModule($id, $data) {
        $module = self::findById($id);
        $module->data = $data;
        return $module->save();
    }

    /**
     * Delete module by id
     *
     * @param $id
     * @return false|int
     * @throws \Exception
     */
    public static function deleteModule($id) {
        $module = self::findById($id);
        return $module->delete();
    }

}