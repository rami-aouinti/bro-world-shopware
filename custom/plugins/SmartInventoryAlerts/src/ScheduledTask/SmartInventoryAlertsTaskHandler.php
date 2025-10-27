<?php declare(strict_types=1);

namespace SmartInventoryAlerts\ScheduledTask;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use SmartInventoryAlerts\Core\Content\Alert\SmartInventoryAlertEntity;
use SmartInventoryAlerts\Service\InventoryAlertService;
use Symfony\Component\Messenger\MessageBusInterface;

class SmartInventoryAlertsTaskHandler extends ScheduledTaskHandler
{
    private EntityRepository $alertRepository;

    private EntityRepository $productRepository;

    private InventoryAlertService $alertService;

    public function __construct(
        MessageBusInterface $messageBus,
        EntityRepository $alertRepository,
        EntityRepository $productRepository,
        InventoryAlertService $alertService
    ) {
        parent::__construct($messageBus);
        $this->alertRepository = $alertRepository;
        $this->productRepository = $productRepository;
        $this->alertService = $alertService;
    }

    public static function getHandledMessages(): iterable
    {
        return [SmartInventoryAlertsTask::class];
    }

    public function run(): void
    {
        $context = Context::createDefaultContext();

        $criteria = (new Criteria())->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('notifiedAt', null));

        $alerts = $this->alertRepository->search($criteria, $context);

        /** @var SmartInventoryAlertEntity $alert */
        foreach ($alerts as $alert) {
            $product = $this->productRepository->search(new Criteria([$alert->getProductId()]), $context)->first();
            if (!$product instanceof ProductEntity) {
                continue;
            }

            if ($product->getStock() > $alert->getThreshold()) {
                $this->alertService->notifyAlert($alert, $product, $context);
            }
        }
    }
}
