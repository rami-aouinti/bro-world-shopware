<?php

declare(strict_types=1);

namespace AdvancedProductCustomization\Cart;

use JsonException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\TaxRuleCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class AdvancedCustomizationCartProcessor implements CartProcessorInterface
{
    public const PAYLOAD_KEY = 'advancedProductCustomization';

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $calculated,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        foreach ($calculated->getLineItems() as $lineItem) {
            if (!$lineItem->hasPayloadValue(self::PAYLOAD_KEY)) {
                $payload = $lineItem->getPayload();
                if (isset($payload[self::PAYLOAD_KEY])) {
                    $lineItem->setPayloadValue(self::PAYLOAD_KEY, $payload[self::PAYLOAD_KEY]);
                } else {
                    continue;
                }
            }

            $configuration = $lineItem->getPayloadValue(self::PAYLOAD_KEY);
            if (\is_string($configuration)) {
                try {
                    $configuration = json_decode($configuration, true, 512, \JSON_THROW_ON_ERROR);
                } catch (JsonException $exception) {
                    continue;
                }
            }

            if (!\is_array($configuration) || empty($configuration['options']) || !$lineItem->getPrice()) {
                continue;
            }

            $options = $configuration['options'];
            $surcharge = $this->calculateSurcharge($options);
            $description = $this->buildDescription($options);

            if ($surcharge !== 0.0) {
                $this->applySurcharge($lineItem, $surcharge);
                $lineItem->setPayloadValue('advancedProductCustomizationSurcharge', $surcharge);
            }

            if ($description !== '') {
                $lineItem->setLabel(sprintf('%s (%s)', $lineItem->getLabel(), $description));
                $lineItem->setPayloadValue('advancedProductCustomizationDescription', $description);
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $options
     */
    private function calculateSurcharge(array $options): float
    {
        $surcharge = 0.0;

        foreach ($options as $option) {
            if (!\is_array($option)) {
                continue;
            }

            $price = (float) ($option['price'] ?? 0);
            $surcharge += $price;
        }

        return $surcharge;
    }

    /**
     * @param array<int, array<string, mixed>> $options
     */
    private function buildDescription(array $options): string
    {
        $labels = [];

        foreach ($options as $option) {
            if (!\is_array($option)) {
                continue;
            }

            $label = (string) ($option['label'] ?? $option['name'] ?? '');
            if ($label !== '') {
                $labels[] = $label;
            }
        }

        return implode(', ', $labels);
    }

    private function applySurcharge(LineItem $lineItem, float $surcharge): void
    {
        $price = $lineItem->getPrice();
        if (!$price instanceof CalculatedPrice) {
            return;
        }

        $unitPrice = $price->getUnitPrice() + $surcharge;
        $totalPrice = $unitPrice * $lineItem->getQuantity();

        $lineItem->setPrice(new CalculatedPrice(
            $unitPrice,
            $totalPrice,
            new CalculatedTaxCollection($price->getCalculatedTaxes()->getElements()),
            new TaxRuleCollection($price->getTaxRules()->getElements()),
            $lineItem->getQuantity(),
            $price->getReferencePrice(),
            $price->getListPrice(),
            $price->getRegulationPrice()
        ));
    }
}
