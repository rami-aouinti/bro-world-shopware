<?php

namespace LoyaltyReferral\Subscriber;

use LoyaltyReferral\Core\Content\LoyaltyTransaction\LoyaltyTransactionEntity;
use LoyaltyReferral\Service\LoyaltyBalanceService;
use LoyaltyReferral\Service\ReferralCodeService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Account\Overview\AccountOverviewPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountPageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $transactionRepository,
        private readonly LoyaltyBalanceService $balanceService,
        private readonly ReferralCodeService $referralCodeService,
        private readonly UrlGeneratorInterface $router
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AccountOverviewPageLoadedEvent::class => 'onAccountOverviewLoaded',
        ];
    }

    public function onAccountOverviewLoaded(AccountOverviewPageLoadedEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();
        $context = $salesChannelContext->getContext();
        $customer = $salesChannelContext->getCustomer();

        if ($customer === null) {
            return;
        }

        $balance = $this->balanceService->getBalanceForCustomer($customer->getId(), $context);
        $code = $this->referralCodeService->getOrCreateCode($customer->getId(), $context);
        $referralUrl = $this->router->generate('frontend.account.login.page', ['ref' => $code], UrlGeneratorInterface::ABSOLUTE_URL);

        $criteria = (new Criteria())
            ->addAssociation('balance')
            ->addFilter(new EqualsFilter('balance.customerId', $customer->getId()))
            ->setLimit(10);
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));

        $transactions = [];
        foreach ($this->transactionRepository->search($criteria, $context)->getEntities() as $transaction) {
            if (!$transaction instanceof LoyaltyTransactionEntity) {
                continue;
            }

            $transactions[] = [
                'amount' => $transaction->getAmount(),
                'type' => $transaction->getType(),
                'description' => $transaction->getDescription(),
            ];
        }

        $event->getPage()->addExtension('loyaltyReferral', new ArrayStruct([
            'balance' => $balance,
            'referralUrl' => $referralUrl,
            'transactions' => $transactions,
        ]));
    }
}
