<?php declare(strict_types=1);

namespace SmartInventoryAlerts\Controller;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"administration"})
 */
class InventoryDashboardController extends AbstractController
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @Route(path="/api/_action/smart-inventory-alerts/dashboard", name="api.smart-inventory-alerts.dashboard", methods={"GET"})
     */
    public function dashboard(Request $request): JsonResponse
    {
        $limit = (int) $request->query->get('limit', 10);
        $limit = max(1, min(100, $limit));

        $lowStock = $this->connection->fetchAllAssociative(
            'SELECT p.id, pt.name, p.stock, p.available_stock
             FROM product p
             INNER JOIN product_translation pt ON pt.product_id = p.id AND pt.language_id = :languageId
             WHERE p.stock <= :threshold AND p.active = 1
             ORDER BY p.stock ASC
             LIMIT ' . $limit,
            [
                'threshold' => 5,
                'languageId' => $this->getLanguageId(),
            ],
            [
                'threshold' => \PDO::PARAM_INT,
                'languageId' => \PDO::PARAM_STR,
            ]
        );

        $slowRotation = $this->connection->fetchAllAssociative(
            'SELECT p.id, pt.name, p.stock, p.sales
             FROM product p
             INNER JOIN product_translation pt ON pt.product_id = p.id AND pt.language_id = :languageId
             WHERE p.sales < :salesThreshold AND p.stock > :stockThreshold AND p.active = 1
             ORDER BY p.sales ASC
             LIMIT ' . $limit,
            [
                'salesThreshold' => 5,
                'stockThreshold' => 0,
                'languageId' => $this->getLanguageId(),
            ],
            [
                'salesThreshold' => \PDO::PARAM_INT,
                'stockThreshold' => \PDO::PARAM_INT,
                'languageId' => \PDO::PARAM_STR,
            ]
        );

        $forecast = $this->connection->fetchAllAssociative(
            'SELECT p.id, pt.name, p.stock, p.sales,
                CASE WHEN p.sales = 0 THEN NULL ELSE ROUND(p.stock / (p.sales / 30), 0) END AS days_left
             FROM product p
             INNER JOIN product_translation pt ON pt.product_id = p.id AND pt.language_id = :languageId
             WHERE p.active = 1
             ORDER BY days_left IS NULL, days_left ASC
             LIMIT ' . $limit,
            [
                'languageId' => $this->getLanguageId(),
            ],
            [
                'languageId' => \PDO::PARAM_STR,
            ]
        );

        return new JsonResponse([
            'lowStock' => $lowStock,
            'slowRotation' => $slowRotation,
            'forecast' => $forecast,
        ]);
    }

    private function getLanguageId(): string
    {
        // default system language (en-GB)
        return hex2bin('2fbb5fe2e29a4d70aa5854ce7ce3e20b');
    }
}
