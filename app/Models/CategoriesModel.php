<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriesModel extends Model
{
    protected $table         = 'categories';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $createdField  = 'ct_created_at';
    protected $updatedField  = 'ct_updated_at';
    protected $allowedFields = [
        'ct_name',
        'ct_products',
        'ct_status',
    ];
}
