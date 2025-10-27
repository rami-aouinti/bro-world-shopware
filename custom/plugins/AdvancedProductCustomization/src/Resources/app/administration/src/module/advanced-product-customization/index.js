import './page/advanced-product-customization-settings';
import './component/advanced-product-customization-product-options';

const { Module } = Shopware;

Module.register('advanced.product.customization', {
    type: 'plugin',
    name: 'AdvancedProductCustomizationModule',
    title: 'advanced-product-customization.general.mainMenuItemGeneral',
    description: 'advanced-product-customization.general.description',
    color: '#ffaa00',
    icon: 'default-badge-help',

    routes: {
        index: {
            component: 'advanced-product-customization-settings',
            path: 'index'
        }
    },

    navigation: [{
        label: 'advanced-product-customization.general.mainMenuItemGeneral',
        color: '#ffaa00',
        path: 'advanced.product.customization.index',
        icon: 'default-badge-help',
        position: 100,
        parent: 'sw-settings'
    }]
});
