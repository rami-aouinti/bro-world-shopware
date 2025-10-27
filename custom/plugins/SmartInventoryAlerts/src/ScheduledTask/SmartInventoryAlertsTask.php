<?php declare(strict_types=1);

namespace SmartInventoryAlerts\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class SmartInventoryAlertsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'smart_inventory_alerts.task';
    }

    public static function getDefaultInterval(): int
    {
        return 900; // 15 minutes
    }
}
