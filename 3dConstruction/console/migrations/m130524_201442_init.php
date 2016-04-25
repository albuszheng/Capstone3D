<?php

use yii\db\Migration;
use common\models\User;

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

        $user = new User();
        $user->username = 'user';
        $user->email = 'user@qq.com';
        $user->user_group = User::GROUP_USER;
        $user->setPassword('user123');
        $user->generateAuthKey();
        $user->save();

        $admin = new User();
        $admin->username = 'admin';
        $admin->email = 'admin@qq.com';
        $admin->user_group = User::GROUP_ADMIN;
        $admin->setPassword('admin123');
        $admin->generateAuthKey();
        $admin->save();

        $engineer = new User();
        $engineer->username = 'engineer';
        $engineer->email = 'engineer@qq.com';
        $engineer->user_group = User::GROUP_ENGINEER;
        $engineer->setPassword('engineer123');
        $engineer->generateAuthKey();
        $engineer->save();

        $staff = new User();
        $staff->username = 'staff';
        $staff->email = 'staff@qq.com';
        $staff->user_group = User::GROUP_STAFF;
        $staff->setPassword('staff123');
        $staff->generateAuthKey();
        $staff->save();

        $this->createTable('{{%model}}', [
            'id' => $this->primaryKey(),
            'size' => $this->string(),
            'scale' => $this->string(),
            'url2d' => $this->string()->notNull(),
            'url3d' => $this->string(),
            'type' => $this->smallInteger()->notNull(),
        ], $tableOptions);

        $this->batchInsert('{{%model}}', [
            'id', 'size', 'scale', 'url2d', 'url3d', 'type'
        ], [
            ['1', null, null, 'null', null, '0'],
            ['2', null, null, 'floor-wood.jpg', null, '0'],
            ['3', null, null, 'wood-2.jpg', null, '0'],
            ['4', null, null, 'brick-wall.jpg', null, '1'],
            ['5', '1,2.1,0.1', '0.02,0.02,0.02', 'door.png', 'door.dae', '2'],
            ['6', '1.44,0.96,0.1', '0.02,0.02,0.02', 'window.png', 'window.dae', '3'],
            ['7', '3,2.5', '0.03,0.03,0.03', 'bed.png', 'bed.dae', '4'],
            ['8', '1.5,1', '0.05,0.05,0.06', 'cabinet.png', 'cabinet.dae', '4'],
            ['9', '1,0.5', '0.02,0.02,0.02', 'drawer.png', 'drawer.dae', '4'],
            ['10', '4,1', '0.04,0.04,0.04', 'TV.png', 'TV.dae', '4'],
            ['11', '3,3', '0.04,0.04,0.04', 'table.png', 'table.dae', '4'],
            ['12', '4,1.5', '0.04,0.04,0.04', 'sofa.png', 'sofa.dae', '4']
        ]);

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

        $this->batchInsert('{{%goods}}', [
            'id', 'price', 'name'
        ], [
            ['1', '10.00', 'beer'],
            ['2', '5.00', 'juice'],
            ['3', '3.00', 'water'],
        ]);

        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'staff_id' => $this->integer()->notNull(),
            'goods' => $this->string()->notNull(),
            'amount' => $this->double(2),
            'time' => $this->string(),
        ], $tableOptions);

        $this->createTable('{{%auth_log}}', [
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

        $this->batchInsert('{{%operation}}', [
            'id', 'operation'
        ], [
            ['1', 'to admin'],
            ['2', 'to engineer'],
            ['3', 'to staff'],
        ]);

    }

    public function down()
    {
        $this->truncateTable('{{%user}}');
        $this->dropTable('{{%user}}');

        $this->truncateTable('{{%model}}');
        $this->dropTable('{{%model}}');

        $this->dropTable('{{%room}}');

        $this->dropTable('{{%floor}}');

        $this->truncateTable('{{%goods}}');
        $this->dropTable('{{%goods}}');

        $this->dropTable('{{%order}}');

        $this->dropTable('{{%auth_log}}');

        $this->truncateTable('{{%operation}}');
        $this->dropTable('{{%operation}}');
    }
}
