<?php

namespace App\Models;

use CodeIgniter\Model;

class SellersModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'us_name',
        'us_email',
        'us_phone',
        'us_role_id',
        'us_password',
        'us_image',
    ];
}
