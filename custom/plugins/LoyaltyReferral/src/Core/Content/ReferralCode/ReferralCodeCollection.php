<?php

namespace LoyaltyReferral\Core\Content\ReferralCode;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ReferralCodeEntity $entity)
 * @method void set(string $key, ReferralCodeEntity $entity)
 * @method ReferralCodeEntity[] getIterator()
 * @method ReferralCodeEntity[] getElements()
 * @method ReferralCodeEntity|null get(string $key)
 * @method ReferralCodeEntity|null first()
 * @method ReferralCodeEntity|null last()
 */
class ReferralCodeCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ReferralCodeEntity::class;
    }
}
