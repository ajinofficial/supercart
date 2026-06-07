<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerAddressToUsersTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        $fields = [];

        if (!$this->db->fieldExists('us_address_line1', 'users')) {
            $fields['us_address_line1'] = [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
                'after' => 'us_phone',
            ];
        }

        if (!$this->db->fieldExists('us_address_line2', 'users')) {
            $fields['us_address_line2'] = [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
                'after' => 'us_address_line1',
            ];
        }

        if (!$this->db->fieldExists('us_city', 'users')) {
            $fields['us_city'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'us_address_line2',
            ];
        }

        if (!$this->db->fieldExists('us_state', 'users')) {
            $fields['us_state'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'us_city',
            ];
        }

        if (!$this->db->fieldExists('us_postal_code', 'users')) {
            $fields['us_postal_code'] = [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'us_state',
            ];
        }

        if (!$this->db->fieldExists('us_country', 'users')) {
            $fields['us_country'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'us_postal_code',
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        $columns = [
            'us_address_line1',
            'us_address_line2',
            'us_city',
            'us_state',
            'us_postal_code',
            'us_country',
        ];

        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, 'users')) {
                $this->forge->dropColumn('users', $column);
            }
        }
    }
}
