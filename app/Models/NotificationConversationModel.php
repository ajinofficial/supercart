<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationConversationModel extends Model
{
    protected $table = 'notification_conversations';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nc_user_id',
        'nc_title',
        'nc_participant',
        'nc_type',
        'nc_avatar',
        'nc_last_message',
        'nc_unread_count',
        'nc_user_unread_count',
        'nc_status',
    ];
}
