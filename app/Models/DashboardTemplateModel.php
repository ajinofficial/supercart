<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardTemplateModel extends Model
{
    protected $table         = 'dashboard_templates';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'dt_name',
        'dt_slug',
        'dt_json',
        'dt_is_active',
        'dt_status',
    ];
}
