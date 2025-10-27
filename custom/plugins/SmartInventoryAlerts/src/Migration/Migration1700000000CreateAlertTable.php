<?php declare(strict_types=1);

namespace SmartInventoryAlerts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1700000000CreateAlertTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1700000000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('CREATE TABLE IF NOT EXISTS `smart_inventory_alert` (
            `id` BINARY(16) NOT NULL,
            `product_id` BINARY(16) NOT NULL,
            `product_version_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NULL,
            `email` VARCHAR(255) NOT NULL,
            `threshold` INT NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `notified_at` DATETIME NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT `fk.smart_inventory_alert.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.smart_inventory_alert.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nothing to do
    }
}
