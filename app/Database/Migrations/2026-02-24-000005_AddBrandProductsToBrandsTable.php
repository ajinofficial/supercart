<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBrandProductsToBrandsTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('brands')) {
            return;
        }

        if ($this->db->fieldExists('br_products', 'brands')) {
            return;
        }

        $this->forge->addColumn('brands', [
            'br_products' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'br_name',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('brands') && $this->db->fieldExists('br_products', 'brands')) {
            $this->forge->dropColumn('brands', 'br_products');
        }
    }
}
