<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $createdField  = 'st_created_at';
    protected $updatedField  = 'st_updated_at';
    protected $allowedFields = [
        'st_group',
        'st_key',
        'st_value',
        'st_type',
    ];
    protected string $lastErrorMessage = '';

    public function __construct(?\CodeIgniter\Database\ConnectionInterface $db = null, ?\CodeIgniter\Validation\ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        if ($this->db->tableExists('settings')) {
            $this->table = 'settings';
        } elseif ($this->db->tableExists('system_settings')) {
            $this->table = 'system_settings';
        }
    }

    public function getGroupSettings(string $group): array
    {
        $rows = $this->db->table($this->table)
            ->select('st_key, st_value')
            ->where('st_group', $group)
            ->get()
            ->getResultArray();
        $mapped = [];

        // Preferred format: one row per group with JSON payload.
        foreach ($rows as $row) {
            $key = trim((string) ($row['st_key'] ?? ''));
            if ($key !== $group) {
                continue;
            }

            $decoded = json_decode((string) ($row['st_value'] ?? ''), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        foreach ($rows as $row) {
            $key = trim((string) ($row['st_key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $mapped[$key] = (string) ($row['st_value'] ?? '');
        }

        return $mapped;
    }

    public function saveGroupSettings(string $group, array $settings): bool
    {
        $db = db_connect();
        $db->transBegin();
        $this->lastErrorMessage = '';

        foreach ($settings as $key => $value) {
            $record = $db->table($this->table)
                ->select('id')
                ->where('st_group', $group)
                ->where('st_key', (string) $key)
                ->get()
                ->getRowArray();

            $payload = [
                'st_group' => $group,
                'st_key'   => (string) $key,
                'st_value' => (string) $value,
                'st_type'  => 'string',
            ];

            if ($record) {
                $ok = $db->table($this->table)
                    ->where('id', (int) $record['id'])
                    ->update($payload);
            } else {
                $ok = $db->table($this->table)->insert($payload);
            }

            if (!$ok) {
                $error = $db->error();
                $this->lastErrorMessage = trim((string) ($error['message'] ?? 'Unable to save settings.'));
                $db->transRollback();
                return false;
            }
        }

        if ($db->transStatus() === false) {
            $error = $db->error();
            $this->lastErrorMessage = trim((string) ($error['message'] ?? 'Unable to save settings.'));
            $db->transRollback();
            return false;
        }

        $db->transCommit();
        return true;
    }

    public function saveGroupAsJson(string $group, array $settings): bool
    {
        $db = db_connect();
        $db->transBegin();
        $this->lastErrorMessage = '';

        $encoded = json_encode($settings, JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            $this->lastErrorMessage = 'Unable to encode settings.';
            $db->transRollback();
            return false;
        }

        $record = $db->table($this->table)
            ->select('id')
            ->where('st_group', $group)
            ->where('st_key', $group)
            ->get()
            ->getRowArray();

        $payload = [
            'st_group' => $group,
            'st_key'   => $group,
            'st_value' => $encoded,
            'st_type'  => 'json',
        ];

        if ($record) {
            $ok = $db->table($this->table)->where('id', (int) $record['id'])->update($payload);
        } else {
            $ok = $db->table($this->table)->insert($payload);
        }

        if (!$ok) {
            $error = $db->error();
            $this->lastErrorMessage = trim((string) ($error['message'] ?? 'Unable to save settings.'));
            $db->transRollback();
            return false;
        }

        // Cleanup legacy key-value rows for this group so it remains one-row JSON.
        $db->table($this->table)
            ->where('st_group', $group)
            ->where('st_key !=', $group)
            ->delete();

        if ($db->transStatus() === false) {
            $error = $db->error();
            $this->lastErrorMessage = trim((string) ($error['message'] ?? 'Unable to save settings.'));
            $db->transRollback();
            return false;
        }

        $db->transCommit();
        return true;
    }

    public function getLastErrorMessage(): string
    {
        return $this->lastErrorMessage;
    }
}
