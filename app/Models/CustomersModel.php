<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomersModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'us_name',
        'us_email',
        'us_phone',
        'us_role_id',
        'us_password',
        'us_image',
        'us_address_line1',
        'us_address_line2',
        'us_city',
        'us_state',
        'us_postal_code',
        'us_country',
    ];
}
