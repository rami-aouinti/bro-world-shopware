<?php

namespace LoyaltyReferral\ScheduledTask;

use LoyaltyReferral\Core\Content\LoyaltyBalance\LoyaltyBalanceEntity;
use LoyaltyReferral\Core\Content\LoyaltyTransaction\LoyaltyTransactionEntity;
use LoyaltyReferral\Service\LoyaltyBalanceService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ExpirePointsTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly EntityRepository $transactionRepository,
        private readonly EntityRepository $balanceRepository,
        private readonly LoyaltyBalanceService $balanceService
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public static function getHandledMessages(): iterable
    {
        return [ExpirePointsTask::class];
    }

    public function run(): void
    {
        $context = Context::createDefaultContext();
        $expiryDate = (new \DateTimeImmutable('-365 days'))->format(\DATE_ATOM);
        $criteria = (new Criteria())
            ->addFilter(new AndFilter([
                new EqualsFilter('type', 'earn'),
                new RangeFilter('createdAt', [RangeFilter::LTE => $expiryDate]),
            ]));

        $transactions = $this->transactionRepository->search($criteria, $context);

        foreach ($transactions->getEntities() as $transaction) {
            if (!$transaction instanceof LoyaltyTransactionEntity) {
                continue;
            }

            $alreadyExpired = $this->transactionRepository->search((new Criteria())
                ->addFilter(new EqualsFilter('description', 'expire:' . $transaction->getUniqueIdentifier())), $context)->getTotal() > 0;

            if ($alreadyExpired) {
                continue;
            }

            $balanceCriteria = (new Criteria([$transaction->getLoyaltyBalanceId()]))->setLimit(1);
            /** @var LoyaltyBalanceEntity|null $balance */
            $balance = $this->balanceRepository->search($balanceCriteria, $context)->first();

            if ($balance === null) {
                continue;
            }

            $this->balanceService->debit(
                $balance->getCustomerId(),
                $transaction->getAmount(),
                'expiry',
                $context,
                null,
                'expire:' . $transaction->getUniqueIdentifier()
            );
        }
    }
}
