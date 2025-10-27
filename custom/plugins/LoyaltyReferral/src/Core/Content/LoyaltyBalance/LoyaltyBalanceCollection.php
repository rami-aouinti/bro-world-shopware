<?php

namespace LoyaltyReferral\Core\Content\LoyaltyBalance;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(LoyaltyBalanceEntity $entity)
 * @method void set(string $key, LoyaltyBalanceEntity $entity)
 * @method LoyaltyBalanceEntity[] getIterator()
 * @method LoyaltyBalanceEntity[] getElements()
 * @method LoyaltyBalanceEntity|null get(string $key)
 * @method LoyaltyBalanceEntity|null first()
 * @method LoyaltyBalanceEntity|null last()
 */
class LoyaltyBalanceCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return LoyaltyBalanceEntity::class;
    }
}
