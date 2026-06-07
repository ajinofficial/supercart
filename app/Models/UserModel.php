<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'us_name',
        'us_email',
        'us_country_code',
        'us_phone',
        'us_role_id',
        'us_password'
    ];

    public function checkUserValidation(array $param)
    {
        $username = isset($param['username']) ? $param['username'] : ""; 
        $email    = isset($param['email']) ? $param['email'] : "";

        $builder  = $this->builder();

        if (!empty($username)) 
        {
            $builder->where('us_name', $username);
        }

        if (!empty($email)) 
        {
            $builder->where('us_email', $email);
        }

        return $builder->countAllResults() > 0;
    }

    public function getUserByEmail(string $email)
    {
        return $this->where('us_email', $email)->first();
    }



}
