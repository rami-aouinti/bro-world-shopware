<?php

namespace LoyaltyReferral\Core\Content\LoyaltyTransaction;

use LoyaltyReferral\Core\Content\LoyaltyBalance\LoyaltyBalanceEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class LoyaltyTransactionEntity extends Entity
{
    use EntityIdTrait;

    protected string $loyaltyBalanceId;

    protected int $amount;

    protected string $type;

    protected ?string $description = null;

    protected ?string $orderId = null;

    protected ?LoyaltyBalanceEntity $balance = null;

    protected ?OrderEntity $order = null;

    public function getLoyaltyBalanceId(): string
    {
        return $this->loyaltyBalanceId;
    }

    public function setLoyaltyBalanceId(string $loyaltyBalanceId): void
    {
        $this->loyaltyBalanceId = $loyaltyBalanceId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getBalance(): ?LoyaltyBalanceEntity
    {
        return $this->balance;
    }

    public function setBalance(?LoyaltyBalanceEntity $balance): void
    {
        $this->balance = $balance;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }
}
