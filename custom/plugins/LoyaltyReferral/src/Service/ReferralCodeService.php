<?php

namespace LoyaltyReferral\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ReferralCodeService
{
    public function __construct(private readonly EntityRepository $referralRepository)
    {
    }

    public function getOrCreateCode(string $customerId, Context $context): string
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('customerId', $customerId));
        $code = $this->referralRepository->search($criteria, $context)->first();

        if ($code !== null) {
            return (string) $code->get('code');
        }

        $generatedCode = strtoupper(substr(sha1($customerId . microtime()), 0, 8));
        $this->referralRepository->create([
            [
                'customerId' => $customerId,
                'code' => $generatedCode,
            ],
        ], $context);

        return $generatedCode;
    }
}
