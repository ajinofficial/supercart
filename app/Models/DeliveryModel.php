<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryModel extends Model
{
    protected $table = 'deliveries';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'dl_created_at';
    protected $updatedField = 'dl_updated_at';
    protected $allowedFields = [
        'dl_shipment_code',
        'dl_order_code',
        'dl_customer_name',
        'dl_hub',
        'dl_rider_name',
        'dl_eta_date',
        'dl_status',
        'dl_priority',
    ];
}

