<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDashboardTemplatesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('dashboard_templates')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'dt_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'dt_slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 140,
            ],
            'dt_json' => [
                'type' => 'LONGTEXT',
            ],
            'dt_is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'dt_status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addUniqueKey('dt_slug');
        $this->forge->createTable('dashboard_templates', true);
    }

    public function down()
    {
        $this->forge->dropTable('dashboard_templates', true);
    }
}
