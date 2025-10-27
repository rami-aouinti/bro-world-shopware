import './module/advanced-product-customization';
import enGB from './snippet/en-GB.json';
import deDE from './snippet/de-DE.json';

const { Module, Locale } = Shopware;

Locale.extend('en-GB', enGB);
Locale.extend('de-DE', deDE);

Module.register('advanced-product-customization', {
    type: 'plugin',
    name: 'AdvancedProductCustomization',
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

    settingsItem: {
        group: 'plugins',
        to: 'advanced.product.customization.index',
        icon: 'default-badge-help'
    }
});
