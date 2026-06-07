<?php

namespace App\Models;

use CodeIgniter\Model;

class BrandsModel extends Model
{
    protected $table         = 'brands';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $createdField  = 'br_created_at';
    protected $updatedField  = 'br_updated_at';
    protected $allowedFields = [
        'br_name',
        'br_products',
        'br_status',
    ];
}
