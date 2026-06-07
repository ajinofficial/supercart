<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('payments')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pm_transaction_code' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
            ],
            'pm_order_code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'pm_customer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'pm_method' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'default' => 'UPI',
            ],
            'pm_gateway_ref' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true,
            ],
            'pm_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'pm_paid_on' => [
                'type' => 'DATE',
            ],
            'pm_status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=pending,2=paid,3=failed,4=refunded',
            ],
            'pm_created_at datetime default current_timestamp',
            'pm_updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('pm_transaction_code');
        $this->forge->createTable('payments', true);

        $builder = $this->db->table('payments');
        $builder->insertBatch([
            [
                'pm_transaction_code' => 'TXN-9001',
                'pm_order_code' => '#1001',
                'pm_customer_name' => 'Rahul Verma',
                'pm_method' => 'UPI',
                'pm_gateway_ref' => 'RAZORPAY-182991',
                'pm_amount' => 1499.00,
                'pm_paid_on' => date('Y-m-d', strtotime('-1 day')),
                'pm_status' => 2,
            ],
            [
                'pm_transaction_code' => 'TXN-9002',
                'pm_order_code' => '#1008',
                'pm_customer_name' => 'Anita Rao',
                'pm_method' => 'Card',
                'pm_gateway_ref' => 'STRIPE-883211',
                'pm_amount' => 2299.00,
                'pm_paid_on' => date('Y-m-d'),
                'pm_status' => 1,
            ],
            [
                'pm_transaction_code' => 'TXN-9003',
                'pm_order_code' => '#1012',
                'pm_customer_name' => 'Sneha Das',
                'pm_method' => 'Net Banking',
                'pm_gateway_ref' => 'PAYU-551100',
                'pm_amount' => 899.00,
                'pm_paid_on' => date('Y-m-d', strtotime('-2 days')),
                'pm_status' => 3,
            ],
            [
                'pm_transaction_code' => 'TXN-9004',
                'pm_order_code' => '#1015',
                'pm_customer_name' => 'Vikram Iyer',
                'pm_method' => 'Wallet',
                'pm_gateway_ref' => 'WALLET-220011',
                'pm_amount' => 799.00,
                'pm_paid_on' => date('Y-m-d', strtotime('-3 days')),
                'pm_status' => 4,
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('payments')) {
            $this->forge->dropTable('payments', true);
        }
    }
}
