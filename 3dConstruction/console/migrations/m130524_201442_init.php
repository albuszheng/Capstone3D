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

        // table user
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

        // table model
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

        // table room
        $this->createTable('{{%room}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'data' => $this->text(),
            'last_modify_id' => $this->integer(),
            'last_modify_time' => $this->string(),
        ], $tableOptions);

        $this->createIndex(
            'idx-room-user_id',
            'room',
            'user_id'
        );

        $this->addForeignKey(
          'fk-room-user_id',
            'room',
            'user_id',
            'user',
            'id',
            'SET NULL'
        );

        $this->createIndex(
            'idx-room-last_modify_id',
            'room',
            'last_modify_id'
        );

        $this->addForeignKey(
            'fk-room-last_modify_id',
            'room',
            'last_modify_id',
            'user',
            'id',
            'SET NULL'
        );

        // 9 floors, 14 rooms for each floor
        for ($i = 1; $i < 10; $i++) {
            for ($j = 1; $j < 15; $j++) {
                $this->insert('{{%room}}', [
                    'id' => $i*100+$j,
                ]);
            }
        }

        // table goods
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

        // table order
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'staff_id' => $this->integer(),
            'price' => $this->double(2)->notNull(),
            'time' => $this->string(),
        ], $tableOptions);

        $this->createIndex(
            'idx-order-user_id',
            'order',
            'user_id'
        );

        $this->addForeignKey(
            'fk-order-user_id',
            'order',
            'user_id',
            'user',
            'id',
            'SET NULL'
        );

        $this->createIndex(
            'idx-order-staff_id',
            'order',
            'staff_id'
        );

        $this->addForeignKey(
            'fk-order-staff_id',
            'order',
            'staff_id',
            'user',
            'id',
            'SET NULL'
        );

        // table order_detail
        $this->createTable('{{%order_detail}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(),
            'goods_id' => $this->integer(),
            'number' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex(
            'idx-order_detail-order_id',
            'order_detail',
            'order_id'
        );

        $this->addForeignKey(
            'fk-order_detail-order_id',
            'order_detail',
            'order_id',
            'order',
            'id',
            'SET NULL'
        );

        $this->createIndex(
            'idx-order_detail-goods_id',
            'order_detail',
            'goods_id'
        );

        $this->addForeignKey(
            'fk-order_detail-goods_id',
            'order_detail',
            'goods_id',
            'goods',
            'id',
            'SET NULL'
        );

        // table operation
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

        // table auth_log
        $this->createTable('{{%auth_log}}', [
            'id' => $this->primaryKey(),
            'operator_id' => $this->integer(),
            'user_id' => $this->integer(),
            'operation_id' => $this->integer(),
            'time' => $this->string(),
        ], $tableOptions);

        $this->createIndex(
            'idx-auth_log-operator_id',
            'auth_log',
            'operator_id'
        );

        $this->addForeignKey(
            'fk-auth_log-operator_id',
            'auth_log',
            'operator_id',
            'user',
            'id',
            'SET NULL'
        );

        $this->createIndex(
            'idx-auth_log-user_id',
            'auth_log',
            'user_id'
        );

        $this->addForeignKey(
            'fk-auth_log-user_id',
            'auth_log',
            'user_id',
            'user',
            'id',
            'SET NULL'
        );

        $this->createIndex(
            'idx-auth_log-operation_id',
            'auth_log',
            'operation_id'
        );

        $this->addForeignKey(
            'fk-auth_log-operation_id',
            'auth_log',
            'operation_id',
            'operation',
            'id',
            'SET NULL'
        );

    }

    public function down()
    {
        $this->dropForeignKey('fk-auth_log-operator_id', 'auth_log');
        $this->dropIndex('idx-auth_log-operator_id', 'auth_log');
        $this->dropForeignKey('fk-auth_log-user_id', 'auth_log');
        $this->dropIndex('idx-auth_log-user_id', 'auth_log');
        $this->dropForeignKey('fk-auth_log-operation_id', 'auth_log');
        $this->dropIndex('idx-auth_log-operation_id', 'auth_log');
        $this->dropTable('{{%auth_log}}');

        $this->truncateTable('{{%operation}}');
        $this->dropTable('{{%operation}}');

        $this->dropForeignKey('fk-order_detail-order_id', 'order_detail');
        $this->dropIndex('idx-order_detail-order_id', 'order_detail');
        $this->dropForeignKey('fk-order_detail-goods_id', 'order_detail');
        $this->dropIndex('idx-order_detail-goods_id', 'order_detail');
        $this->dropTable('{{%order_detail}}');

        $this->dropForeignKey('fk-order-user_id', 'order');
        $this->dropIndex('idx-order-user_id', 'order');
        $this->dropForeignKey('fk-order-staff_id', 'order');
        $this->dropIndex('idx-order-staff_id', 'order');
        $this->dropTable('{{%order}}');

        $this->truncateTable('{{%goods}}');
        $this->dropTable('{{%goods}}');

        $this->dropForeignKey('fk-room-user_id', 'room');
        $this->dropIndex('idx-room-user_id', 'room');
        $this->dropForeignKey('fk-room-last_modify_id', 'room');
        $this->dropIndex('idx-room-last_modify_id', 'room');
        $this->dropTable('{{%room}}');

        $this->truncateTable('{{%model}}');
        $this->dropTable('{{%model}}');

        $this->truncateTable('{{%user}}');
        $this->dropTable('{{%user}}');
    }
}
