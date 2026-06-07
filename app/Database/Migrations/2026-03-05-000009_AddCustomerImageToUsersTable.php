<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerImageToUsersTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        if ($this->db->fieldExists('us_image', 'users')) {
            return;
        }

        $this->forge->addColumn('users', [
            'us_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'us_password',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('us_image', 'users')) {
            $this->forge->dropColumn('users', 'us_image');
        }
    }
}
