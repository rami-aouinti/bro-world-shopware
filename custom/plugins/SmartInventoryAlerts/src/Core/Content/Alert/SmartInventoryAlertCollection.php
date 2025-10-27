<?php declare(strict_types=1);

namespace SmartInventoryAlerts\Core\Content\Alert;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                             add(SmartInventoryAlertEntity $entity)
 * @method void                             set(string $key, SmartInventoryAlertEntity $entity)
 * @method SmartInventoryAlertEntity[]      getIterator()
 * @method SmartInventoryAlertEntity[]      getElements()
 * @method SmartInventoryAlertEntity|null   get(string $key)
 * @method SmartInventoryAlertEntity|null   first()
 * @method SmartInventoryAlertEntity|null   last()
 */
class SmartInventoryAlertCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SmartInventoryAlertEntity::class;
    }
}
