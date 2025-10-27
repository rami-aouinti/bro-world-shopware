<?php declare(strict_types=1);

namespace SmartInventoryAlerts;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\DataAbstractionLayer\Migration\MigrationCollectionLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SmartInventoryAlerts extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
    }

    public function postInstall(InstallContext $installContext): void
    {
        parent::postInstall($installContext);
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        $uninstallContext->setKeepUserData(true);
        parent::uninstall($uninstallContext);
    }
}
