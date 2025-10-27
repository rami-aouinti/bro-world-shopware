import SmartInventoryAlert from './script/smart-inventory-alert';

const PluginManager = window.PluginManager;

PluginManager.register('SmartInventoryAlert', SmartInventoryAlert, '[data-smart-inventory-alert-form]');
