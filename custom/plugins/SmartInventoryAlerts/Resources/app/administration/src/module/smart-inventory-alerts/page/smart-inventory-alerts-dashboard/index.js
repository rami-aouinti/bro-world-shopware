import template from './smart-inventory-alerts-dashboard.html.twig';
import './smart-inventory-alerts-dashboard.scss';

const { Component, Mixin, Service } = Shopware;

Component.register('smart-inventory-alerts-dashboard', {
    template,

    inject: ['SmartInventoryAlertsApiService'],

    data() {
        return {
            isLoading: false,
            lowStock: [],
            slowRotation: [],
            forecast: []
        };
    },

    created() {
        this.loadData();
    },

    methods: {
        loadData() {
            this.isLoading = true;
            this.SmartInventoryAlertsApiService.fetchDashboard(10)
                .then((response) => {
                    this.lowStock = response.lowStock || [];
                    this.slowRotation = response.slowRotation || [];
                    this.forecast = response.forecast || [];
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        getProductLabel(entry) {
            return entry.name || entry.id;
        }
    }
});
