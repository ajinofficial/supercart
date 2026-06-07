<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('coupons')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'cp_title' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'cp_code' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
            ],
            'cp_type' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=percentage,2=fixed',
            ],
            'cp_value' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
            ],
            'cp_min_order' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
            ],
            'cp_max_discount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'cp_start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'cp_end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'cp_usage_limit' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'cp_used_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'cp_status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'cp_created_at datetime default current_timestamp',
            'cp_updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('cp_code');
        $this->forge->createTable('coupons', true);
    }

    public function down()
    {
        if ($this->db->tableExists('coupons')) {
            $this->forge->dropTable('coupons', true);
        }
    }
}
