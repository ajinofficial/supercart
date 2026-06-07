<?php

namespace App\Models;

use CodeIgniter\Model;

class CouponsModel extends Model
{
    protected $table = 'coupons';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'cp_created_at';
    protected $updatedField = 'cp_updated_at';
    protected $allowedFields = [
        'cp_title',
        'cp_code',
        'cp_type',
        'cp_value',
        'cp_min_order',
        'cp_max_discount',
        'cp_start_date',
        'cp_end_date',
        'cp_usage_limit',
        'cp_used_count',
        'cp_status',
    ];
}
