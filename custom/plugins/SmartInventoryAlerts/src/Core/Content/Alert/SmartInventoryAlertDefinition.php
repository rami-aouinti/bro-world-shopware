<?php declare(strict_types=1);

namespace SmartInventoryAlerts\Core\Content\Alert;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

class SmartInventoryAlertDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'smart_inventory_alert';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SmartInventoryAlertEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SmartInventoryAlertCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new IdField('product_id', 'productId'))->addFlags(new Required()),
            new ReferenceVersionField(ProductDefinition::class),
            (new StringField('email', 'email'))->addFlags(new Required()),
            (new IntField('threshold', 'threshold'))->addFlags(new Required()),
            (new BoolField('active', 'active'))->addFlags(new Required()),
            new DateTimeField('notified_at', 'notifiedAt'),
            new IdField('language_id', 'languageId'),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false),
            new ManyToOneAssociationField('language', 'language_id', LanguageDefinition::class, 'id', false),
        ]);
    }
}
