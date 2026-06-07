<?php

namespace App\Models;

use CodeIgniter\Model;

class ReturnsModel extends Model
{
    protected $table = 'returns';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'rt_created_at';
    protected $updatedField = 'rt_updated_at';
    protected $allowedFields = [
        'rt_return_code',
        'rt_order_code',
        'rt_customer_name',
        'rt_reason',
        'rt_requested_on',
        'rt_status',
        'rt_refund_amount',
        'rt_refund_mode',
        'rt_refund_state',
    ];
}
