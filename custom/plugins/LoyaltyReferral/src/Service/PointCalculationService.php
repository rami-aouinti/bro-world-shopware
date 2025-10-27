<?php

namespace LoyaltyReferral\Service;

use Shopware\Core\Checkout\Order\OrderEntity;

class PointCalculationService
{
    public function calculateEarnedPoints(OrderEntity $order, float $multiplier = 1.0): int
    {
        $totalPrice = $order->getPrice()?->getTotalPrice() ?? 0.0;
        $points = (int) round($totalPrice * $multiplier);

        return max(0, $points);
    }

    public function calculateRedeemablePoints(int $requestedPoints, int $balance): int
    {
        return max(0, min($requestedPoints, $balance));
    }
}
