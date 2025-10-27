<?php declare(strict_types=1);

namespace SmartInventoryAlerts\Storefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Csrf\Annotation\Csrf;
use SmartInventoryAlerts\Service\InventoryAlertService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class InventoryAlertController extends StorefrontController
{
    private InventoryAlertService $alertService;

    public function __construct(InventoryAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * @Route(path="/smart-inventory-alerts/register", name="frontend.smart-inventory-alerts.register", methods={"POST"})
     * @Csrf("smart_inventory_alert_form")
     */
    public function register(Request $request, SalesChannelContext $context): JsonResponse
    {
        $productId = (string) $request->request->get('productId');
        $email = (string) $request->request->get('email');
        $threshold = (int) $request->request->get('threshold', 1);

        if (!Uuid::isValid($productId) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['success' => false, 'message' => 'Paramètres invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->alertService->registerAlert($productId, $email, max(1, $threshold), $context->getContext(), $context->getLanguageId());

        return new JsonResponse(['success' => true]);
    }
}
