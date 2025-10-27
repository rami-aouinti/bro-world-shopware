<?php

namespace LoyaltyReferral\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CreditPointsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'loyalty_referral.credit_points';
    }

    public static function getDefaultInterval(): int
    {
        return 300;
    }
}
