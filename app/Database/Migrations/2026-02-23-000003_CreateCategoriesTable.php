<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('categories')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ct_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'ct_products' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'ct_status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'ct_created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'ct_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('categories', true);
    }

    public function down()
    {
        $this->forge->dropTable('categories', true);
    }
}
