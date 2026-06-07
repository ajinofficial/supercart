<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductDiscountToProductsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('products')) {
            return;
        }

        if ($this->db->fieldExists('pr_discount', 'products')) {
            return;
        }

        $this->forge->addColumn('products', [
            'pr_discount' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0,
                'after'      => 'pr_price',
            ],
        ]);
    }

    public function down()
    {
        if (!$this->db->tableExists('products')) {
            return;
        }

        if (!$this->db->fieldExists('pr_discount', 'products')) {
            return;
        }

        $this->forge->dropColumn('products', 'pr_discount');
    }
}
