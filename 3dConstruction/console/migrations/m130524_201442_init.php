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
            'name' => $this->string(),
            'size' => $this->string(),
            'scale' => $this->string(),
            'url2d' => $this->string()->notNull(),
            'url3d' => $this->string(),
            'type' => $this->smallInteger()->notNull(),
        ], $tableOptions);

        $this->batchInsert('{{%model}}', [
            'id', 'name', 'size', 'scale', 'url2d', 'url3d', 'type'
        ], [
            ['1', '地板0', null, null, 'null', null, '0'],
            ['2', '地板1', null, null, 'floor-wood.jpg', null, '0'],
            ['3', '地板2', null, null, 'wood-2.jpg', null, '0'],
            ['4', '墙', null, null, 'brick-wall.jpg', null, '1'],
            ['5', '门', '1,2.1,0.1', '0.02,0.02,0.02', 'door.png', 'door.dae', '2'],
            ['6', '窗', '1.44,0.96,0.1', '0.02,0.02,0.02', 'window.png', 'window.dae', '3'],
            ['7', '床', '3,2.5', '0.03,0.03,0.03', 'bed.png', 'bed.dae', '4'],
            ['8', '橱柜', '1.5,1', '0.05,0.05,0.06', 'cabinet.png', 'cabinet.dae', '4'],
            ['9', '抽屉', '1,0.5', '0.02,0.02,0.02', 'drawer.png', 'drawer.dae', '4'],
            ['10', '电视', '4,1', '0.04,0.04,0.04', 'TV.png', 'TV.dae', '4'],
            ['11', '桌子', '3,3', '0.04,0.04,0.04', 'table.png', 'table.dae', '4'],
            ['12', '沙发', '4,1.5', '0.04,0.04,0.04', 'sofa.png', 'sofa.dae', '4'],
            ['13', '传感器', '0.2,0.3,0.1', '0.06,0.05,0.06', 'sensor.png', 'sensor.dae', '5']
        ]);

        // table module
        $this->createTable('{{%module}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'size' => $this->string(),
            'data' => $this->text(),
        ], $tableOptions);

        $this->batchInsert('{{%module}}', [
            'id', 'name', 'size', 'data'
        ], [
            ['1', '两室一厅', '20,20', '{"version":"1.0.0","type":"scene","floor":{"type":"floor","width":20,"height":20,"id":"1"},"wall":[{"type":"wall","id":"4","size":[19.9,0.1],"position":[0.1,10],"rotation":1.5,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[19.9,0.1],"position":[19.9,10],"rotation":0.5,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[19.9,0.1],"position":[10,19.9],"rotation":1,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[19.9,0.1],"position":[10,0.1],"rotation":0,"doors":[],"windows":[{"type":"window","id":"6","position":[14.5927734375,0.1],"rotation":0},{"type":"window","id":"6","position":[3.7177734375,0.1],"rotation":0}],"sensors":[]},{"type":"wall","id":"4","size":[8.21875,0.1],"position":[11.6865234375,4.1875],"rotation":0.5,"doors":[{"type":"door","id":"5","position":[11.6865234375,4.1875],"rotation":0.5}],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[8.1875,0.1],"position":[15.7802734375,8.25],"rotation":0,"doors":[],"windows":[],"sensors":[{"type":"sensor","id":"13","position":[18.4990234375,8.25],"rotation":0}]},{"type":"wall","id":"4","size":[8.15625,0.1],"position":[7.2490234375,4.1875],"rotation":0.5,"doors":[{"type":"door","id":"5","position":[7.2490234375,5.71875],"rotation":0.5}],"windows":[],"sensors":[{"type":"sensor","id":"13","position":[7.2490234375,7.90625],"rotation":0.5}]},{"type":"wall","id":"4","size":[7.156249999999999,0.1],"position":[3.7490234375,8.3125],"rotation":0,"doors":[],"windows":[{"type":"window","id":"6","position":[3.7490234375,8.3125],"rotation":0}],"sensors":[]}],"objects":[{"type":"furniture","id":"7","position":[14.5302734375,1.03125],"rotation":0.5},{"type":"furniture","id":"7","position":[1.5927734375,0.7499999999999999],"rotation":0.5},{"type":"furniture","id":"9","position":[4.9365234375,1.34375],"rotation":0},{"type":"furniture","id":"8","position":[17.5927734375,2.0625],"rotation":0},{"type":"furniture","id":"9","position":[12.9677734375,1.625],"rotation":0},{"type":"furniture","id":"11","position":[14.6865234375,17.34375],"rotation":0},{"type":"furniture","id":"10","position":[17.8740234375,6.03125],"rotation":1},{"type":"furniture","id":"12","position":[5.9052734375,16.5625],"rotation":1},{"type":"furniture","id":"10","position":[1.8740234375,13.46875],"rotation":0}]}'],
            ['2', '三室一厅', '30,30', '{"version":"1.0.0","type":"scene","floor":{"type":"floor","width":30,"height":30,"id":"1"},"wall":[{"type":"wall","id":"4","size":[29.899999999999995,0.1],"position":[0.1,15],"rotation":1.5,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[29.899999999999995,0.1],"position":[29.899999999999995,15],"rotation":0.5,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[29.899999999999995,0.1],"position":[15,29.899999999999995],"rotation":1,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[29.899999999999995,0.1],"position":[15,0.1],"rotation":0,"doors":[],"windows":[{"type":"window","id":"6","position":[14.5927734375,0.1],"rotation":0},{"type":"window","id":"6","position":[3.7177734375,0.1],"rotation":0}],"sensors":[]},{"type":"wall","id":"4","size":[8.21875,0.1],"position":[11.6865234375,4.1875],"rotation":0.5,"doors":[{"type":"door","id":"5","position":[11.6865234375,4.1875],"rotation":0.5}],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[8.1875,0.1],"position":[15.7802734375,8.25],"rotation":0,"doors":[],"windows":[],"sensors":[{"type":"sensor","id":"13","position":[18.4990234375,8.25],"rotation":0}]},{"type":"wall","id":"4","size":[8.15625,0.1],"position":[7.249023437499999,4.1875],"rotation":0.5,"doors":[{"type":"door","id":"5","position":[7.249023437499999,5.71875],"rotation":0.5}],"windows":[],"sensors":[{"type":"sensor","id":"13","position":[7.249023437499999,7.90625],"rotation":0.5}]},{"type":"wall","id":"4","size":[7.15625,0.1],"position":[3.7490234374999996,8.3125],"rotation":0,"doors":[],"windows":[{"type":"window","id":"6","position":[3.7490234374999996,8.3125],"rotation":0}],"sensors":[]},{"type":"wall","id":"4","size":[8.0625,0.1],"position":[19.82666015625,4.171875],"rotation":0.5,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[0,0.1],"position":[23.953125,0.234375],"rotation":0,"doors":[],"windows":[],"sensors":[]},{"type":"wall","id":"4","size":[7.78125,0.1],"position":[21.70166015625,25.96875],"rotation":0.5,"doors":[],"windows":[{"type":"window","id":"6","position":[21.70166015625,25.96875],"rotation":0.5}],"sensors":[]},{"type":"wall","id":"4","size":[8.296875,0.1],"position":[25.77978515625,22.078125],"rotation":0,"doors":[{"type":"door","id":"5","position":[27.60791015625,22.078125],"rotation":0}],"windows":[],"sensors":[{"type":"sensor","id":"13","position":[22.59228515625,22.078125],"rotation":0}]}],"objects":[{"type":"furniture","id":"7","position":[14.530273437499998,1.03125],"rotation":0.5},{"type":"furniture","id":"7","position":[1.5927734375,0.75],"rotation":0.5},{"type":"furniture","id":"9","position":[4.9365234375,1.34375],"rotation":0},{"type":"furniture","id":"8","position":[17.5927734375,2.0625],"rotation":0},{"type":"furniture","id":"9","position":[12.9677734375,1.625],"rotation":0},{"type":"furniture","id":"11","position":[16.54541015625,14.625],"rotation":0},{"type":"furniture","id":"10","position":[17.8740234375,6.03125],"rotation":1},{"type":"furniture","id":"12","position":[7.40478515625,26.4375],"rotation":1},{"type":"furniture","id":"10","position":[3.56103515625,22.546875],"rotation":0},{"type":"furniture","id":"7","position":[22.73291015625,29.15625],"rotation":0},{"type":"furniture","id":"8","position":[23.67041015625,24.796875],"rotation":1.5},{"type":"furniture","id":"11","position":[23.01416015625,18.375],"rotation":0}]}']
        ]);

        // table building
        $this->createTable('{{%building}}', [
            'id' => $this->primaryKey(),
            'building_no' => $this->integer(),
            'floor' => $this->integer(),
            'x_axis' => $this->integer(),
            'y_axis' => $this->integer(),
            'width' => $this->integer(),
            'height' => $this->integer(),
        ], $tableOptions);

     /*   // table floor
        $this->createTable('{{%floor}}', [
            'id' => $this->primaryKey(),
            'floor_no' => $this->integer(),
            'building_id' => $this->integer(),
            'data' => $this->text(),
            'last_modify_id' => $this->integer(),
            'last_modify_time' => $this->string(),
        ], $tableOptions);

        $this->createIndex(
            'idx-floor-building_id',
            'floor',
            'building_id'
        );

        $this->addForeignKey(
            'fk-floor-building_id',
            'floor',
            'building_id',
            'building',
            'id',
            'SET NULL'
        );

        $this->createIndex(
            'idx-floor-last_modify_id',
            'floor',
            'last_modify_id'
        );

        $this->addForeignKey(
            'fk-floor-last_modify_id',
            'floor',
            'last_modify_id',
            'user',
            'id',
            'SET NULL'
        );*/

        // table room
        $this->createTable('{{%room}}', [
            'id' => $this->primaryKey(),
            'room_no' => $this->integer(),
            'floor_no' => $this->integer(),
            'building_id' => $this->integer(),
            'size' => $this->string(),
            'position' => $this->string(),
            'user_id' => $this->integer(),
            'data' => $this->text(),
            'last_modify_id' => $this->integer(),
            'last_modify_time' => $this->string(),
        ], $tableOptions);

        $this->createIndex(
            'idx-room-building_id',
            'room',
            'building_id'
        );

        $this->addForeignKey(
            'fk-room-building_id',
            'room',
            'building_id',
            'building',
            'id',
            'CASCADE'
        );

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
            'NO ACTION'
        );

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
            'NO ACTION'
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
            'NO ACTION'
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
            'NO ACTION'
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
            'NO ACTION'
        );

        // table operation
        $this->createTable('{{%operation}}', [
            'id' => $this->primaryKey(),
            'operation' => $this->string()->notNull(),
            'user_group' => $this->smallInteger()->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->batchInsert('{{%operation}}', [
            'id', 'operation', 'user_group'
        ], [
            ['1', 'to admin', User::GROUP_ADMIN],
            ['2', 'to engineer', User::GROUP_ENGINEER],
            ['3', 'to staff', User::GROUP_STAFF],
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
            'NO ACTION'
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
            'NO ACTION'
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
            'NO ACTION'
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

        $this->dropForeignKey('fk-room-last_modify_id', 'room');
        $this->dropIndex('idx-room-last_modify_id', 'room');
        $this->dropForeignKey('fk-room-user_id', 'room');
        $this->dropIndex('idx-room-user_id', 'room');
        $this->dropForeignKey('fk-room-building_id', 'room');
        $this->dropIndex('idx-room-building_id', 'room');
        $this->dropTable('{{%room}}');

//        $this->dropForeignKey('fk-floor-building_id', 'floor');
//        $this->dropIndex('idx-floor-building_id', 'floor');
//        $this->dropForeignKey('fk-floor-last_modify_id', 'floor');
//        $this->dropIndex('idx-floor-last_modify_id', 'floor');
//        $this->dropTable('{{%floor}}');

        $this->truncateTable('{{%building}}');
        $this->dropTable('{{%building}}');

        $this->truncateTable('{{%module}}');
        $this->dropTable('{{%module}}');

        $this->truncateTable('{{%model}}');
        $this->dropTable('{{%model}}');

        $this->truncateTable('{{%user}}');
        $this->dropTable('{{%user}}');
    }
}
