<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBrandsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('brands')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'br_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'br_products' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'br_status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'br_created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'br_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('brands', true);
    }

    public function down()
    {
        $this->forge->dropTable('brands', true);
    }
}
