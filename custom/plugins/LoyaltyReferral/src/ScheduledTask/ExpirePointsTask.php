<?php

namespace LoyaltyReferral\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ExpirePointsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'loyalty_referral.expire_points';
    }

    public static function getDefaultInterval(): int
    {
        return 86400;
    }
}
