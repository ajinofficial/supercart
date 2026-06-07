<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationMessageModel extends Model
{
    protected $table = 'notification_messages';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'nm_conversation_id',
        'nm_sender_type',
        'nm_sender_name',
        'nm_message',
        'nm_is_read',
        'created_at',
    ];
}
