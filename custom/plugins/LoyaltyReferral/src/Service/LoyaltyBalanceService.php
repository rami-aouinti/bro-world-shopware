<?php

namespace LoyaltyReferral\Service;

use LoyaltyReferral\Core\Content\LoyaltyBalance\LoyaltyBalanceEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class LoyaltyBalanceService
{
    public function __construct(private readonly EntityRepository $balanceRepository, private readonly EntityRepository $transactionRepository)
    {
    }

    public function getBalanceForCustomer(string $customerId, Context $context): int
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('customerId', $customerId));
        /** @var LoyaltyBalanceEntity|null $balanceEntity */
        $balanceEntity = $this->balanceRepository->search($criteria, $context)->first();

        return $balanceEntity?->getBalance() ?? 0;
    }

    public function credit(string $customerId, int $points, string $type, Context $context, ?string $orderId = null, ?string $description = null): void
    {
        $this->applyDelta($customerId, abs($points), $type, $context, $orderId, $description);
    }

    public function debit(string $customerId, int $points, string $type, Context $context, ?string $orderId = null, ?string $description = null): void
    {
        $this->applyDelta($customerId, -1 * abs($points), $type, $context, $orderId, $description);
    }

    private function applyDelta(string $customerId, int $delta, string $type, Context $context, ?string $orderId, ?string $description): void
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('customerId', $customerId));
        /** @var LoyaltyBalanceEntity|null $balanceEntity */
        $balanceEntity = $this->balanceRepository->search($criteria, $context)->first();

        if ($balanceEntity === null) {
            $newBalance = max(0, $delta);
            $balanceId = Uuid::randomHex();
            $this->balanceRepository->create([
                [
                    'id' => $balanceId,
                    'customerId' => $customerId,
                    'balance' => $newBalance,
                ],
            ], $context);
        } else {
            $balanceId = $balanceEntity->getUniqueIdentifier();
            $newBalance = max(0, $balanceEntity->getBalance() + $delta);
            $this->balanceRepository->update([
                [
                    'id' => $balanceId,
                    'balance' => $newBalance,
                ],
            ], $context);
        }

        if ($balanceId === null) {
            return;
        }

        $this->transactionRepository->create([
            [
                'loyaltyBalanceId' => $balanceId,
                'amount' => $delta,
                'type' => $type,
                'orderId' => $orderId,
                'description' => $description,
            ],
        ], $context);
    }
}
