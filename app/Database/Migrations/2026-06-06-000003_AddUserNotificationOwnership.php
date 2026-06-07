<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserNotificationOwnership extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('notification_conversations')) {
            return;
        }

        $fields = $this->db->getFieldNames('notification_conversations');
        $add = [];

        if (!in_array('nc_user_id', $fields, true)) {
            $add['nc_user_id'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'id',
            ];
        }

        if (!in_array('nc_user_unread_count', $fields, true)) {
            $add['nc_user_unread_count'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'after' => 'nc_unread_count',
            ];
        }

        if ($add !== []) {
            $this->forge->addColumn('notification_conversations', $add);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('notification_conversations')) {
            return;
        }

        $fields = $this->db->getFieldNames('notification_conversations');
        foreach (['nc_user_unread_count', 'nc_user_id'] as $field) {
            if (in_array($field, $fields, true)) {
                $this->forge->dropColumn('notification_conversations', $field);
            }
        }
    }
}
