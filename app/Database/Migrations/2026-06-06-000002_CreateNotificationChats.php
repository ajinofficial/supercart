<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationChats extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('notification_conversations')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nc_title' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'nc_participant' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'nc_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'customer',
                ],
                'nc_avatar' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'nc_last_message' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'nc_unread_count' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'default' => 0,
                ],
                'nc_status' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('nc_status');
            $this->forge->addKey('updated_at');
            $this->forge->createTable('notification_conversations', true);
        }

        if (!$this->db->tableExists('notification_messages')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nm_conversation_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'nm_sender_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'system',
                ],
                'nm_sender_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'nm_message' => [
                    'type' => 'TEXT',
                ],
                'nm_is_read' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['nm_conversation_id', 'id']);
            $this->forge->addKey('nm_is_read');
            $this->forge->addForeignKey(
                'nm_conversation_id',
                'notification_conversations',
                'id',
                'CASCADE',
                'CASCADE'
            );
            $this->forge->createTable('notification_messages', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('notification_messages', true);
        $this->forge->dropTable('notification_conversations', true);
    }
}
