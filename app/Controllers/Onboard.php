<?php

namespace App\Controllers;

use App\Models\AccountsModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database as DatabaseConfig;
use Config\GlobalSettings;

class Onboard extends BaseController
{
    protected AccountsModel $accounts;

    public function __construct()
    {
        $this->accounts = new AccountsModel();
        helper(['form', 'url']);
    }

    public function index(): string
    {
        return view('onboard/index');
    }

    public function create(): ResponseInterface
    {
        $domain = $this->domainFromRequest();
        $gmail = strtolower(trim((string) $this->request->getPost('gmail')));

        if (!$this->isValidDomain($domain)) {
            return $this->jsonResponse(false, 'Please enter a valid domain name.', [], 422);
        }

        if (!filter_var($gmail, FILTER_VALIDATE_EMAIL) || !str_ends_with($gmail, '@gmail.com')) {
            return $this->jsonResponse(false, 'Please enter a valid Gmail address.', [], 422);
        }

        try {
            $this->ensureAccountsTable();

            if ($this->accounts->domainExists($domain)) {
                return $this->jsonResponse(false, 'This domain is already onboarded.');
            }

            if ($this->accounts->gmailExists($gmail)) {
                return $this->jsonResponse(false, 'This Gmail address is already onboarded.');
            }

            $accountName = $this->domainPrefix($domain);
            $databaseName = $this->databaseNameFromAccountName($accountName);
            $userPassword = $accountName;
            $userPasswordHash = password_hash($userPassword, PASSWORD_DEFAULT);
            $databaseConfig = config(DatabaseConfig::class)->default;

            $this->createTenantDatabase($databaseName, $domain, $gmail, $userPasswordHash);

            $saved = $this->accounts->insert([
                'acct_name' => $accountName,
                'acct_domain' => $domain,
                'acct_gmail' => $gmail,
                'acct_db_host' => (string) $databaseConfig['hostname'],
                'acct_db_name' => $databaseName,
                'acct_db_username' => (string) $databaseConfig['username'],
                'acct_db_password' => (string) $databaseConfig['password'],
                'acct_us_password' => $userPasswordHash,
                'acct_status' => 1,
                'acct_free_trial_days' => GlobalSettings::FREE_TRIAL_DAYS,
                'acct_deleted' => 0,
            ]);

            if (!$saved) {
                return $this->jsonResponse(false, 'Account could not be saved.', [], 500);
            }

            return $this->jsonResponse(
                true,
                'Account onboarded and database created successfully.',
                [
                    'id' => $this->accounts->getInsertID(),
                    'acct_name' => $accountName,
                    'acct_db_name' => $databaseName,
                    'acct_free_trial_days' => GlobalSettings::FREE_TRIAL_DAYS,
                ]
            );
        } catch (\Throwable $e) {
            return $this->jsonResponse(false, $e->getMessage(), [], 500);
        }
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = preg_replace('#/.*$#', '', $domain) ?? $domain;

        return trim($domain, ". \t\n\r\0\x0B");
    }

    private function domainFromRequest(): string
    {
        $domain = (string) $this->request->getPost('domain');
        $domainName = (string) $this->request->getPost('domain_name');

        if (trim($domainName) !== '') {
            $domain = trim($domainName) . GlobalSettings::TENANT_DOMAIN_SUFFIX;
        }

        return $this->normalizeDomain($domain);
    }

    private function isValidDomain(string $domain): bool
    {
        return $domain !== ''
            && strlen($domain) <= 253
            && (bool) preg_match('/^(?!-)(?:[a-z0-9-]{1,63}\.)+[a-z]{2,63}$/', $domain);
    }

    private function databaseNameFromAccountName(string $accountName): string
    {
        $name = preg_replace('/[^a-z0-9]+/', '_', strtolower($accountName)) ?? $accountName;
        $name = trim($name, '_');

        return GlobalSettings::TENANT_DATABASE_PREFIX . substr($name, 0, 58);
    }

    private function createTenantDatabase(
        string $databaseName,
        string $domain,
        string $gmail,
        string $userPasswordHash
    ): void
    {
        $db = db_connect();
        $escapedName = '`' . str_replace('`', '``', $databaseName) . '`';
        $db->query('CREATE DATABASE IF NOT EXISTS ' . $escapedName . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

        $tenantDb = $this->tenantConnection($databaseName);
        $this->runStagingSql($tenantDb);
        $this->runTenantMigrations($tenantDb);
        $this->insertTenantAdmin($tenantDb, $domain, $gmail, $userPasswordHash);
    }

    private function tenantConnection(string $databaseName): BaseConnection
    {
        $databaseConfig = config(DatabaseConfig::class);
        $tenantConfig = $databaseConfig->default;
        $tenantConfig['database'] = $databaseName;

        return db_connect($tenantConfig);
    }

    private function runStagingSql(BaseConnection $tenantDb): void
    {
        foreach (GlobalSettings::STAGING_TABLE_SQL as $sql) {
            $tenantDb->query($sql);
        }
    }

    private function runTenantMigrations(BaseConnection $tenantDb): void
    {
        $runner = new MigrationRunner(config('Migrations'), $tenantDb);
        $runner->setNamespace(APP_NAMESPACE)->latest();
    }

    private function insertTenantAdmin(
        BaseConnection $tenantDb,
        string $domain,
        string $gmail,
        string $userPasswordHash
    ): void
    {
        if (!$tenantDb->tableExists('users')) {
            throw new \RuntimeException('Users table was not created in the tenant database.');
        }

        $accountName = $this->domainPrefix($domain);
        $existingAdmin = $tenantDb->table('users')
            ->where('us_email', $gmail)
            ->where('us_role_id', 1)
            ->countAllResults();

        if ($existingAdmin > 0) {
            return;
        }

        $tenantDb->table('users')->insert([
            'us_name' => $accountName,
            'us_email' => $gmail,
            'us_country_code' => '',
            'us_phone' => '',
            'us_role_id' => 1,
            'us_password' => $userPasswordHash,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function domainPrefix(string $domain): string
    {
        $parts = explode('.', $domain, 2);

        return trim($parts[0]) !== '' ? $parts[0] : $domain;
    }

    private function ensureAccountsTable(): void
    {
        $db = db_connect();

        if ($db->tableExists('accounts')) {
            $this->upgradeAccountsTable($db);

            return;
        }

        $forge = DatabaseConfig::forge();
        $forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'acct_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'acct_domain' => [
                'type' => 'VARCHAR',
                'constraint' => 253,
            ],
            'acct_gmail' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'acct_db_host' => [
                'type' => 'VARCHAR',
                'constraint' => 253,
            ],
            'acct_db_name' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'acct_db_username' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'acct_db_password' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'acct_us_password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'acct_status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'acct_free_trial_days' => [
                'type' => 'SMALLINT',
                'constraint' => 5,
                'unsigned' => true,
                'default' => GlobalSettings::FREE_TRIAL_DAYS,
            ],
            'acct_deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'acct_created_at datetime default current_timestamp',
            'acct_updated_at datetime default current_timestamp on update current_timestamp',
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey('acct_domain');
        $forge->addUniqueKey('acct_gmail');
        $forge->addUniqueKey('acct_db_name');
        $forge->createTable('accounts', true);
    }

    private function upgradeAccountsTable(BaseConnection $db): void
    {
        $fields = $db->getFieldNames('accounts');
        $legacyColumns = [
            'domain' => 'acct_domain',
            'gmail' => 'acct_gmail',
            'database_name' => 'acct_db_name',
            'status' => 'acct_status',
            'created_at' => 'acct_created_at',
            'updated_at' => 'acct_updated_at',
        ];
        $definitions = [
            'domain' => 'VARCHAR(253) NOT NULL',
            'gmail' => 'VARCHAR(191) NOT NULL',
            'database_name' => 'VARCHAR(64) NOT NULL',
            'status' => "VARCHAR(20) NOT NULL DEFAULT 'active'",
            'created_at' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ];

        foreach ($legacyColumns as $legacy => $prefixed) {
            if (in_array($legacy, $fields, true) && !in_array($prefixed, $fields, true)) {
                $db->query(
                    sprintf(
                        'ALTER TABLE `accounts` CHANGE `%s` `%s` %s',
                        $legacy,
                        $prefixed,
                        $definitions[$legacy]
                    )
                );
            }
        }

        $fields = $db->getFieldNames('accounts');

        if (in_array('acct_id', $fields, true) && !in_array('id', $fields, true)) {
            $db->query(
                'ALTER TABLE `accounts` CHANGE `acct_id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT'
            );
        }

        $fields = $db->getFieldNames('accounts');
        $newColumns = [
            'acct_name' => "VARCHAR(120) NOT NULL DEFAULT '' AFTER `id`",
            'acct_db_host' => "VARCHAR(253) NOT NULL DEFAULT '' AFTER `acct_gmail`",
            'acct_db_username' => "VARCHAR(191) NOT NULL DEFAULT '' AFTER `acct_db_name`",
            'acct_db_password' => 'TEXT NULL AFTER `acct_db_username`',
            'acct_us_password' => "VARCHAR(255) NOT NULL DEFAULT '' AFTER `acct_db_password`",
            'acct_free_trial_days' => 'SMALLINT(5) UNSIGNED NOT NULL DEFAULT '
                . GlobalSettings::FREE_TRIAL_DAYS
                . ' AFTER `acct_status`',
            'acct_deleted' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `acct_status`',
        ];

        foreach ($newColumns as $column => $definition) {
            if (!in_array($column, $fields, true)) {
                $db->query(sprintf('ALTER TABLE `accounts` ADD `%s` %s', $column, $definition));
            }
        }

        $db->query(
            "UPDATE `accounts`
             SET `acct_name` = SUBSTRING_INDEX(`acct_domain`, '.', 1)
             WHERE `acct_name` = ''"
        );

        $statusField = null;

        foreach ($db->getFieldData('accounts') as $field) {
            if ($field->name === 'acct_status') {
                $statusField = $field;
                break;
            }
        }

        if ($statusField !== null && stripos((string) $statusField->type, 'tinyint') === false) {
            $db->query(
                "UPDATE `accounts`
                 SET `acct_status` = CASE
                     WHEN LOWER(TRIM(`acct_status`)) IN ('1', 'active', 'enabled', 'true') THEN '1'
                     ELSE '0'
                 END"
            );
            $db->query(
                'ALTER TABLE `accounts`
                 MODIFY `acct_status` TINYINT(1) NOT NULL DEFAULT 1'
            );
        }
    }

    private function jsonResponse(
        bool $success,
        string $message,
        array $data = [],
        int $statusCode = 200
    ): ResponseInterface {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'success' => $success,
                'message' => $message,
                'data' => $data,
                'csrf' => [
                    'name' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
    }
}
