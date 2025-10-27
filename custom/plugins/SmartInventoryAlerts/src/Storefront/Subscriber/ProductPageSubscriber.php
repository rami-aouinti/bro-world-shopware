<?php declare(strict_types=1);

namespace SmartInventoryAlerts\Storefront\Subscriber;

use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductLoaded',
        ];
    }

    public function onProductLoaded(ProductPageLoadedEvent $event): void
    {
        $extension = new ArrayEntity([
            'defaultThreshold' => 1,
            'productId' => $event->getPage()->getProduct()->getId(),
        ]);

        $event->getPage()->addExtension('smartInventoryAlert', $extension);
    }
}
