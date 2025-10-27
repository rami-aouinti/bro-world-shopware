import './page/smart-inventory-alerts-dashboard';
import './snippet';
import './service/smart-inventory-alerts.api.service';

Shopware.Module.register('smart-inventory-alerts', {
    type: 'plugin',
    name: 'SmartInventoryAlerts',
    title: 'smart-inventory-alerts.general.mainMenuItemGeneral',
    description: 'smart-inventory-alerts.general.descriptionTextModule',
    color: '#0b74de',
    icon: 'default-symbol-products',
    routes: {
        index: {
            component: 'smart-inventory-alerts-dashboard',
            path: 'index'
        }
    },
    navigation: [{
        label: 'smart-inventory-alerts.general.mainMenuItemGeneral',
        color: '#0b74de',
        path: 'smart.inventory.alerts.index',
        position: 100,
        parent: 'sw-catalogue'
    }]
});
