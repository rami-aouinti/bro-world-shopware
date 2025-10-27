<?php

namespace LoyaltyReferral\Core\Content\LoyaltyTransaction;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(LoyaltyTransactionEntity $entity)
 * @method void set(string $key, LoyaltyTransactionEntity $entity)
 * @method LoyaltyTransactionEntity[] getIterator()
 * @method LoyaltyTransactionEntity[] getElements()
 * @method LoyaltyTransactionEntity|null get(string $key)
 * @method LoyaltyTransactionEntity|null first()
 * @method LoyaltyTransactionEntity|null last()
 */
class LoyaltyTransactionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return LoyaltyTransactionEntity::class;
    }
}
