<?php

namespace LoyaltyReferral\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1700000000LoyaltyReferral extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1700000000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<SQL
            CREATE TABLE IF NOT EXISTS `loyalty_balance` (
                `id` BINARY(16) NOT NULL,
                `customer_id` BINARY(16) NOT NULL,
                `balance` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.loyalty_balance.customer_id` FOREIGN KEY (`customer_id`)
                    REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        $connection->executeStatement(<<<SQL
            CREATE TABLE IF NOT EXISTS `loyalty_transaction` (
                `id` BINARY(16) NOT NULL,
                `loyalty_balance_id` BINARY(16) NOT NULL,
                `amount` INT NOT NULL,
                `type` VARCHAR(64) NOT NULL,
                `order_id` BINARY(16) NULL,
                `description` VARCHAR(255) NULL,
                `created_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.loyalty_transaction.balance_id` FOREIGN KEY (`loyalty_balance_id`)
                    REFERENCES `loyalty_balance` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.loyalty_transaction.order_id` FOREIGN KEY (`order_id`)
                    REFERENCES `order` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        $connection->executeStatement(<<<SQL
            CREATE TABLE IF NOT EXISTS `referral_code` (
                `id` BINARY(16) NOT NULL,
                `customer_id` BINARY(16) NOT NULL,
                `code` VARCHAR(64) NOT NULL,
                `usage_count` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
                `updated_at` DATETIME(3) NULL,
                UNIQUE KEY `uniq.referral_code.code` (`code`),
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.referral_code.customer_id` FOREIGN KEY (`customer_id`)
                    REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeStatement('DROP TABLE IF EXISTS `loyalty_transaction`;');
        $connection->executeStatement('DROP TABLE IF EXISTS `loyalty_balance`;');
        $connection->executeStatement('DROP TABLE IF EXISTS `referral_code`;');
    }
}
