<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull(),

            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'user_group' => $this->smallInteger()->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createTable('{{%model}}', [
            'id' => $this->primaryKey(),
            'size' => $this->string(),
            'scale' => $this->string(),
            'url2d' => $this->string()->notNull(),
            'url3d' => $this->string(),
            'type' => $this->smallInteger()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%room}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'data' => $this->text(),
            'last_modify_id' => $this->integer(),
            'last_modify_time' => $this->string(),
        ], $tableOptions);

        $this->createTable('{{%floor}}', [
            'id' => $this->primaryKey(),
            'data' => $this->text(),
            'last_modify_id' => $this->integer(),
            'last_modify_time' => $this->string(),
        ], $tableOptions);

        $this->createTable('{{%goods}}', [
            'id' => $this->primaryKey(),
            'price' => $this->double(2),
            'name' => $this->string(),
        ], $tableOptions);

        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'staff_id' => $this->integer()->notNull(),
            'goods' => $this->string()->notNull(),
            'amount' => $this->double(2),
            'time' => $this->string(),
        ], $tableOptions);

        $this->createTable('{{%authority_log}}', [
            'id' => $this->primaryKey(),
            'operator_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'operation_id' => $this->integer()->notNull(),
            'time' => $this->string(),
        ], $tableOptions);

        $this->createTable('{{%operation}}', [
            'id' => $this->primaryKey(),
            'operation' => $this->string()->notNull(),
        ], $tableOptions);

    }

    public function down()
    {
        $this->dropTable('{{%user}}');
        $this->dropTable('{{%model}}');
        $this->dropTable('{{%room}}');
        $this->dropTable('{{%floor}}');
        $this->dropTable('{{%goods}}');
        $this->dropTable('{{%order}}');
        $this->dropTable('{{%authority_log}}');
        $this->dropTable('{{%operation}}');
    }
}
