<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemSettingsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('settings')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'st_group' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'st_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'st_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'st_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'string',
            ],
            'st_created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'st_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('st_key');
        $this->forge->createTable('settings', true);
    }

    public function down()
    {
        $this->forge->dropTable('settings', true);
    }
}
