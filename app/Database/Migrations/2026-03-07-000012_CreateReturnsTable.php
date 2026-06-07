<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReturnsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('returns')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'rt_return_code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'rt_order_code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'rt_customer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'rt_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
            ],
            'rt_requested_on' => [
                'type' => 'DATE',
            ],
            'rt_status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=requested,2=picked_up,3=in_inspection,4=completed,5=rejected',
            ],
            'rt_refund_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'rt_refund_mode' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'default' => 'Original Payment',
            ],
            'rt_refund_state' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=pending,2=processing,3=refunded,4=declined',
            ],
            'rt_created_at datetime default current_timestamp',
            'rt_updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rt_return_code');
        $this->forge->createTable('returns', true);

        $builder = $this->db->table('returns');
        $builder->insertBatch([
            [
                'rt_return_code' => 'RT-5001',
                'rt_order_code' => '#1001',
                'rt_customer_name' => 'Rahul Verma',
                'rt_reason' => 'Wrong size delivered',
                'rt_requested_on' => date('Y-m-d', strtotime('-1 day')),
                'rt_status' => 1,
                'rt_refund_amount' => 1499.00,
                'rt_refund_mode' => 'Wallet',
                'rt_refund_state' => 1,
            ],
            [
                'rt_return_code' => 'RT-5002',
                'rt_order_code' => '#1008',
                'rt_customer_name' => 'Anita Rao',
                'rt_reason' => 'Defective item',
                'rt_requested_on' => date('Y-m-d', strtotime('-2 days')),
                'rt_status' => 3,
                'rt_refund_amount' => 2299.00,
                'rt_refund_mode' => 'UPI',
                'rt_refund_state' => 2,
            ],
            [
                'rt_return_code' => 'RT-5003',
                'rt_order_code' => '#1011',
                'rt_customer_name' => 'Sneha Das',
                'rt_reason' => 'Product not as shown',
                'rt_requested_on' => date('Y-m-d', strtotime('-4 days')),
                'rt_status' => 4,
                'rt_refund_amount' => 899.00,
                'rt_refund_mode' => 'Original Payment',
                'rt_refund_state' => 3,
            ],
            [
                'rt_return_code' => 'RT-5004',
                'rt_order_code' => '#1013',
                'rt_customer_name' => 'Vikram Iyer',
                'rt_reason' => 'Damaged in transit',
                'rt_requested_on' => date('Y-m-d', strtotime('-3 days')),
                'rt_status' => 5,
                'rt_refund_amount' => 0.00,
                'rt_refund_mode' => 'Original Payment',
                'rt_refund_state' => 4,
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('returns')) {
            $this->forge->dropTable('returns', true);
        }
    }
}
