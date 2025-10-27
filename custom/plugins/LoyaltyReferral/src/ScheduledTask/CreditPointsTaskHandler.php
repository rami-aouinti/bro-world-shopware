<?php

namespace LoyaltyReferral\ScheduledTask;

use LoyaltyReferral\Service\LoyaltyBalanceService;
use LoyaltyReferral\Service\PointCalculationService;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class CreditPointsTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        private readonly EntityRepository $scheduledTaskRepository,
        private readonly EntityRepository $orderRepository,
        private readonly LoyaltyBalanceService $balanceService,
        private readonly PointCalculationService $pointCalculationService
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public static function getHandledMessages(): iterable
    {
        return [CreditPointsTask::class];
    }

    public function run(): void
    {
        $context = Context::createDefaultContext();
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('transactions.stateMachineState.technicalName', 'paid'))
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('customFields.loyalty_referral_processed', false),
                new EqualsFilter('customFields.loyalty_referral_processed', null),
            ]));

        /** @var OrderCollection $orders */
        $orders = $this->orderRepository->search($criteria, $context)->getEntities();
        foreach ($orders as $order) {
            $customerId = $order->getOrderCustomer()?->getCustomerId();
            if ($customerId === null) {
                continue;
            }

            $points = $this->pointCalculationService->calculateEarnedPoints($order);
            if ($points <= 0) {
                continue;
            }

            $this->balanceService->credit($customerId, $points, 'earn', $context, $order->getId(), 'Order payment reward');

            $this->orderRepository->update([
                [
                    'id' => $order->getId(),
                    'customFields' => array_merge($order->getCustomFields() ?? [], [
                        'loyalty_referral_processed' => true,
                    ]),
                ],
            ], $context);
        }
    }
}
