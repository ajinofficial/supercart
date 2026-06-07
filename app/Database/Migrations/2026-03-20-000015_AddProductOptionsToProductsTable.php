<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductOptionsToProductsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('products')) {
            return;
        }

        if ($this->db->fieldExists('pr_options', 'products')) {
            return;
        }

        $this->forge->addColumn('products', [
            'pr_options' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'pr_discount',
            ],
        ]);
    }

    public function down()
    {
        if (!$this->db->tableExists('products')) {
            return;
        }

        if (!$this->db->fieldExists('pr_options', 'products')) {
            return;
        }

        $this->forge->dropColumn('products', 'pr_options');
    }
}
