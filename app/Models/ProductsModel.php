<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductsModel extends Model
{
    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'id',
        'pr_name',
        'pr_description',
        'pr_image',
        'pr_category',
        'pr_brand',
        'pr_stock',
        'pr_price',
        'pr_discount',
        'pr_options',
        'pr_status',
    ];
}
