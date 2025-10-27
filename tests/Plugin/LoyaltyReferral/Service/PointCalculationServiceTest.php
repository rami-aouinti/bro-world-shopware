<?php

namespace Tests\Plugin\LoyaltyReferral\Service;

use LoyaltyReferral\Service\PointCalculationService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderPrice;

class PointCalculationServiceTest extends TestCase
{
    public function testCalculateEarnedPointsRoundsTotal(): void
    {
        $service = new PointCalculationService();
        $order = $this->createOrderWithTotal(49.75);

        static::assertSame(50, $service->calculateEarnedPoints($order));
    }

    public function testCalculateEarnedPointsWithMultiplier(): void
    {
        $service = new PointCalculationService();
        $order = $this->createOrderWithTotal(120.15);

        static::assertSame(180, $service->calculateEarnedPoints($order, 1.5));
    }

    public function testCalculateRedeemablePoints(): void
    {
        $service = new PointCalculationService();

        static::assertSame(50, $service->calculateRedeemablePoints(50, 100));
        static::assertSame(75, $service->calculateRedeemablePoints(90, 75));
        static::assertSame(0, $service->calculateRedeemablePoints(-5, 75));
    }

    private function createOrderWithTotal(float $total): OrderEntity
    {
        $order = $this->createMock(OrderEntity::class);
        $price = $this->createConfiguredMock(OrderPrice::class, [
            'getTotalPrice' => $total,
        ]);
        $order->method('getPrice')->willReturn($price);

        return $order;
    }
}
