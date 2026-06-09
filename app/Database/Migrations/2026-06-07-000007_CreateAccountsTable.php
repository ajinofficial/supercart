<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccountsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('accounts')) {
            return;
        }

        $this->forge->addField([
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
                'default' => 14,
            ],
            'acct_deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'acct_created_at datetime default current_timestamp',
            'acct_updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('acct_domain');
        $this->forge->addUniqueKey('acct_gmail');
        $this->forge->addUniqueKey('acct_db_name');
        $this->forge->createTable('accounts', true);
    }

    public function down()
    {
        if ($this->db->tableExists('accounts')) {
            $this->forge->dropTable('accounts', true);
        }
    }
}
