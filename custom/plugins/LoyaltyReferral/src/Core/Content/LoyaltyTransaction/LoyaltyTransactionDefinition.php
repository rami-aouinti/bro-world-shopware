<?php

namespace LoyaltyReferral\Core\Content\LoyaltyTransaction;

use LoyaltyReferral\Core\Content\LoyaltyBalance\LoyaltyBalanceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Checkout\Order\OrderDefinition;

class LoyaltyTransactionDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'loyalty_transaction';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return LoyaltyTransactionEntity::class;
    }

    public function getCollectionClass(): string
    {
        return LoyaltyTransactionCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('loyalty_balance_id', 'loyaltyBalanceId', LoyaltyBalanceDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('balance', 'loyalty_balance_id', LoyaltyBalanceDefinition::class, 'id'),
            (new IntField('amount', 'amount'))->addFlags(new Required()),
            (new StringField('type', 'type', 64))->addFlags(new Required()),
            new FkField('order_id', 'orderId', OrderDefinition::class),
            new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id', false),
            new StringField('description', 'description', 255),
        ]);
    }
}
