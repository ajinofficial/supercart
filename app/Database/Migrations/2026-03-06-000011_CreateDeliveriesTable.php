<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeliveriesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('deliveries')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'dl_shipment_code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'dl_order_code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'dl_customer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'dl_hub' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'dl_rider_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'dl_eta_date' => [
                'type' => 'DATE',
            ],
            'dl_status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=processing,2=out_for_delivery,3=delivered,4=delayed',
            ],
            'dl_priority' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=normal,2=high,3=critical',
            ],
            'dl_created_at datetime default current_timestamp',
            'dl_updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('dl_shipment_code');
        $this->forge->createTable('deliveries', true);

        $builder = $this->db->table('deliveries');
        $builder->insertBatch([
            [
                'dl_shipment_code' => 'DL-2201',
                'dl_order_code' => '#1001',
                'dl_customer_name' => 'Rahul Verma',
                'dl_hub' => 'Chennai',
                'dl_rider_name' => 'Naveen',
                'dl_eta_date' => date('Y-m-d'),
                'dl_status' => 2,
                'dl_priority' => 2,
            ],
            [
                'dl_shipment_code' => 'DL-2202',
                'dl_order_code' => '#1002',
                'dl_customer_name' => 'Anita Rao',
                'dl_hub' => 'Mumbai',
                'dl_rider_name' => 'Pravin',
                'dl_eta_date' => date('Y-m-d', strtotime('+1 day')),
                'dl_status' => 1,
                'dl_priority' => 1,
            ],
            [
                'dl_shipment_code' => 'DL-2203',
                'dl_order_code' => '#1003',
                'dl_customer_name' => 'Sneha Das',
                'dl_hub' => 'Bengaluru',
                'dl_rider_name' => 'Arun',
                'dl_eta_date' => date('Y-m-d', strtotime('-1 day')),
                'dl_status' => 4,
                'dl_priority' => 3,
            ],
            [
                'dl_shipment_code' => 'DL-2204',
                'dl_order_code' => '#1004',
                'dl_customer_name' => 'Vikram Iyer',
                'dl_hub' => 'Hyderabad',
                'dl_rider_name' => 'Salim',
                'dl_eta_date' => date('Y-m-d'),
                'dl_status' => 3,
                'dl_priority' => 1,
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('deliveries')) {
            $this->forge->dropTable('deliveries', true);
        }
    }
}

