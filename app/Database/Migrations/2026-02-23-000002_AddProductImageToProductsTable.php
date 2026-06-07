<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductImageToProductsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('products')) {
            return;
        }

        if ($this->db->fieldExists('pr_image', 'products')) {
            return;
        }

        $this->forge->addColumn('products', [
            'pr_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'pr_name',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('products') && $this->db->fieldExists('pr_image', 'products')) {
            $this->forge->dropColumn('products', 'pr_image');
        }
    }
}
