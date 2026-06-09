<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountsModel extends Model
{
    protected $table = 'accounts';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'acct_name',
        'acct_domain',
        'acct_gmail',
        'acct_db_host',
        'acct_db_name',
        'acct_db_username',
        'acct_db_password',
        'acct_us_password',
        'acct_status',
        'acct_free_trial_days',
        'acct_deleted',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'acct_created_at';
    protected $updatedField = 'acct_updated_at';

    public function domainExists(string $domain): bool
    {
        return $this->where('acct_domain', $domain)->countAllResults() > 0;
    }

    public function gmailExists(string $gmail): bool
    {
        return $this->where('acct_gmail', $gmail)->countAllResults() > 0;
    }
}
