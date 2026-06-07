<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductReviewsModel extends Model
{
    protected $table = 'product_reviews';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'product_id',
        'user_id',
        'rating',
        'review_text',
        'is_verified_purchase',
        'status',
    ];
}
