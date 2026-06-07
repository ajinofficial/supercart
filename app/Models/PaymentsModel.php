<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentsModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'pm_created_at';
    protected $updatedField = 'pm_updated_at';
    protected $allowedFields = [
        'pm_transaction_code',
        'pm_order_code',
        'pm_customer_name',
        'pm_method',
        'pm_gateway_ref',
        'pm_amount',
        'pm_paid_on',
        'pm_status',
    ];
}
