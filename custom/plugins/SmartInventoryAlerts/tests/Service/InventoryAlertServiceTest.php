<?php declare(strict_types=1);

namespace SmartInventoryAlerts\Tests\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Test\TestBuilder\Product\ProductBuilder;
use Shopware\Core\Test\TestDataCollection;
use Shopware\Core\System\Mail\Service\MailServiceInterface;
use SmartInventoryAlerts\Core\Content\Alert\SmartInventoryAlertEntity;
use SmartInventoryAlerts\Service\InventoryAlertService;

class InventoryAlertServiceTest extends TestCase
{
    use IntegrationTestBehaviour;

    private EntityRepository $alertRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alertRepository = $this->getContainer()->get('smart_inventory_alert.repository');
    }

    public function testRegisterAlertCreatesEntity(): void
    {
        $context = Context::createDefaultContext();
        $productId = $this->createProduct($context);
        $service = new InventoryAlertService($this->alertRepository, new NullMailService());

        $service->registerAlert($productId, 'client@example.com', 2, $context, null);

        $criteria = (new Criteria())->addFilter(new EqualsFilter('productId', $productId));
        $result = $this->alertRepository->search($criteria, $context);

        static::assertSame(1, $result->count());
        /** @var SmartInventoryAlertEntity $alert */
        $alert = $result->first();
        static::assertSame('client@example.com', $alert->getEmail());
        static::assertSame(2, $alert->getThreshold());
    }

    public function testNotifyAlertDisablesSubscription(): void
    {
        $context = Context::createDefaultContext();
        $productId = $this->createProduct($context, 10, 5);
        $service = new InventoryAlertService($this->alertRepository, new NullMailService());

        $alertId = Uuid::randomHex();
        $this->alertRepository->create([
            [
                'id' => $alertId,
                'productId' => $productId,
                'productVersionId' => $context->getVersionId(),
                'email' => 'client@example.com',
                'threshold' => 2,
                'active' => true,
            ],
        ], $context);

        $product = $this->getContainer()->get('product.repository')->search(new Criteria([$productId]), $context)->first();
        static::assertInstanceOf(ProductEntity::class, $product);

        $alert = $this->alertRepository->search(new Criteria([$alertId]), $context)->first();
        static::assertInstanceOf(SmartInventoryAlertEntity::class, $alert);

        $service->notifyAlert($alert, $product, $context);

        $updated = $this->alertRepository->search(new Criteria([$alertId]), $context)->first();
        static::assertInstanceOf(SmartInventoryAlertEntity::class, $updated);
        static::assertFalse($updated->isActive());
        static::assertNotNull($updated->getNotifiedAt());
    }

    private function createProduct(Context $context, int $stock = 5, int $sales = 0): string
    {
        $ids = new TestDataCollection();
        $productId = $ids->create('product');
        $taxId = $ids->create('tax');

        $this->getContainer()->get('tax.repository')->create([
            [
                'id' => $taxId,
                'taxRate' => 19,
                'name' => 'Test tax',
            ],
        ], $context);

        $builder = (new ProductBuilder($productId))
            ->name('Test product')
            ->productNumber('SIA-' . random_int(1000, 9999))
            ->price(100)
            ->taxId($taxId)
            ->stock($stock)
            ->sales($sales)
            ->active(true);

        $this->getContainer()->get('product.repository')->create([
            $builder->build(),
        ], $context);

        return $productId;
    }
}

class NullMailService implements MailServiceInterface
{
    public array $messages = [];

    public function send(array $data, Context $context, array $templateData = []): void
    {
        $this->messages[] = $data;
    }
}
