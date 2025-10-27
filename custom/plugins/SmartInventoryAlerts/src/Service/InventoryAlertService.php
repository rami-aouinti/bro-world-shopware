<?php declare(strict_types=1);

namespace SmartInventoryAlerts\Service;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Mail\Service\MailServiceInterface;
use SmartInventoryAlerts\Core\Content\Alert\SmartInventoryAlertEntity;

class InventoryAlertService
{
    private EntityRepository $alertRepository;

    private MailServiceInterface $mailService;

    public function __construct(EntityRepository $alertRepository, MailServiceInterface $mailService)
    {
        $this->alertRepository = $alertRepository;
        $this->mailService = $mailService;
    }

    public function registerAlert(string $productId, string $email, int $threshold, Context $context, ?string $languageId = null): void
    {
        $alertData = [
            'id' => Uuid::randomHex(),
            'productId' => $productId,
            'productVersionId' => $context->getVersionId(),
            'email' => $email,
            'threshold' => $threshold,
            'active' => true,
            'languageId' => $languageId,
        ];

        $existingCriteria = (new Criteria())
            ->addFilter(new EqualsFilter('productId', $productId))
            ->addFilter(new EqualsFilter('email', $email))
            ->addFilter(new EqualsFilter('active', true));

        $existing = $this->alertRepository->searchIds($existingCriteria, $context);

        if ($existing->getTotal() > 0) {
            return;
        }

        $this->alertRepository->create([$alertData], $context);
    }

    public function notifyAlert(SmartInventoryAlertEntity $alert, ProductEntity $product, Context $context): void
    {
        $subject = sprintf('Votre produit %s est de retour en stock', $product->getTranslation('name'));
        $content = sprintf(
            "Bonjour,\n\nLe produit %s est maintenant disponible avec %d articles en stock.\n\nMerci pour votre patience.",
            $product->getTranslation('name'),
            $product->getStock()
        );

        $mailData = [
            'recipients' => [
                $alert->getEmail() => $alert->getEmail(),
            ],
            'subject' => $subject,
            'contentPlain' => $content,
            'contentHtml' => nl2br($content),
        ];

        $this->mailService->send($mailData, $context, ['product' => $product]);

        $this->alertRepository->update([
            [
                'id' => $alert->getUniqueIdentifier(),
                'active' => false,
                'notifiedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            ],
        ], $context);
    }
}
