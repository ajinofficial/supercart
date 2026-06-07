<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdersModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'order_code',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_note',
        'payment_method',
        'status',
        'subtotal',
        'discount',
        'total',
        'coupon_code',
        'items_json',
    ];
}

