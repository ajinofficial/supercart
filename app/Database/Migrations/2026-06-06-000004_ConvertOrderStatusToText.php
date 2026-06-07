<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConvertOrderStatusToText extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('orders') || !$this->db->fieldExists('status', 'orders')) {
            return;
        }

        $this->db->query(
            "ALTER TABLE orders MODIFY status VARCHAR(20) NOT NULL DEFAULT 'processing'"
        );
        $this->db->query(
            "UPDATE orders
             SET status = CASE
                 WHEN status = '2' THEN 'delivered'
                 WHEN status = '3' THEN 'cancelled'
                 WHEN LOWER(status) IN ('processing', 'delivered', 'cancelled') THEN LOWER(status)
                 ELSE 'processing'
             END"
        );
    }

    public function down()
    {
        if (!$this->db->tableExists('orders') || !$this->db->fieldExists('status', 'orders')) {
            return;
        }

        $this->db->query(
            "UPDATE orders
             SET status = CASE
                 WHEN status = 'delivered' THEN '2'
                 WHEN status = 'cancelled' THEN '3'
                 ELSE '1'
             END"
        );
        $this->db->query(
            'ALTER TABLE orders MODIFY status INT(5) NOT NULL DEFAULT 1'
        );
    }
}
