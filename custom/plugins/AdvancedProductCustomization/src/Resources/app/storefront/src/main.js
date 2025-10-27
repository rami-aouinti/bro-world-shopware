import AdvancedProductCustomizationPlugin from './plugin/advanced-product-customization/advanced-product-customization.plugin';
import './scss/advanced-product-customization.scss';

const PluginManager = window.PluginManager;

PluginManager.register('AdvancedProductCustomization', AdvancedProductCustomizationPlugin, '[data-advanced-product-customization]');
