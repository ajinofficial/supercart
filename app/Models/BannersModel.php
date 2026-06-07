<?php

namespace App\Models;

use CodeIgniter\Model;

class BannersModel extends Model
{
    protected $table = 'banners';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'bn_created_at';
    protected $updatedField = 'bn_updated_at';
    protected $allowedFields = [
        'bn_title',
        'bn_image',
        'bn_link',
        'bn_status',
    ];
}
