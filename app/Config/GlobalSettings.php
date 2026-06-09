<?php

namespace Config;

final class GlobalSettings
{
    public const DATABASE_HOST = 'localhost';

    public const DATABASE_USERNAME = 'root';

    public const DATABASE_PASSWORD = '';

    public const MAIN_DATABASE = 'online_cart';

    public const TENANT_DATABASE_PREFIX = 'acct_';

    public const TENANT_DOMAIN_SUFFIX = '.supercart.com';

    public const FREE_TRIAL_DAYS = 14;

    public const STAGING_TABLE_SQL = [
        <<<'SQL'
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `us_name` VARCHAR(120) NOT NULL,
    `us_email` VARCHAR(191) NOT NULL,
    `us_country_code` VARCHAR(10) NULL,
    `us_phone` VARCHAR(40) NULL,
    `us_role_id` INT(11) NOT NULL DEFAULT 2,
    `us_password` VARCHAR(255) NOT NULL,
    `us_image` VARCHAR(255) NULL,
    `us_address_line1` VARCHAR(200) NULL,
    `us_address_line2` VARCHAR(200) NULL,
    `us_city` VARCHAR(100) NULL,
    `us_state` VARCHAR(100) NULL,
    `us_postal_code` VARCHAR(20) NULL,
    `us_country` VARCHAR(100) NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_us_email_unique` (`us_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
    ];

    private function __construct()
    {
    }
}
