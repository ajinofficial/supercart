<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductDescriptionToProductsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('products')) {
            return;
        }

        if ($this->db->fieldExists('pr_description', 'products')) {
            return;
        }

        $this->forge->addColumn('products', [
            'pr_description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'pr_name',
            ],
        ]);
    }

    public function down()
    {
        if (!$this->db->tableExists('products')) {
            return;
        }

        if (!$this->db->fieldExists('pr_description', 'products')) {
            return;
        }

        $this->forge->dropColumn('products', 'pr_description');
    }
}
