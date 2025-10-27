import template from './advanced-product-customization-settings.html.twig';
import './advanced-product-customization-settings.scss';

const { Component, Context, Data, Mixin, Utils } = Shopware;
const { Criteria } = Data;

Component.register('advanced-product-customization-settings', {
    template,
    inject: ['repositoryFactory'],
    mixins: [Mixin.getByName('notification')],

    data() {
        return {
            isLoading: false,
            selectedProductId: null,
            selectedProduct: null,
            productOptions: []
        };
    },

    computed: {
        hasSelection() {
            return !!this.selectedProductId;
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        }
    },

    watch: {
        selectedProductId: {
            immediate: false,
            handler(productId) {
                this.loadProduct(productId);
            }
        }
    },

    methods: {
        async loadProduct(productId) {
            if (!productId) {
                this.selectedProduct = null;
                this.productOptions = [];
                return;
            }

            this.isLoading = true;

            const criteria = new Criteria(1);
            criteria.addAssociation('translations');

            try {
                this.selectedProduct = await this.productRepository.get(productId, Context.api, criteria);
                const customFields = this.selectedProduct.customFields || {};
                this.productOptions = Array.isArray(customFields.advanced_product_customization_options)
                    ? customFields.advanced_product_customization_options.map((option) => ({
                        id: option.id || Utils.createId(),
                        name: option.name || option.label || '',
                        label: option.label || option.name || '',
                        price: option.price || 0,
                        description: option.description || ''
                    }))
                    : [];
            } catch (error) {
                this.createNotificationError({
                    message: this.$tc('global.default.errorMessage')
                });
            } finally {
                this.isLoading = false;
            }
        },

        onOptionsChange(options) {
            this.productOptions = options.map((option) => ({
                ...option,
                label: option.name || option.label || '',
            }));
        },

        onAddOption() {
            this.productOptions.push({
                id: Utils.createId(),
                name: '',
                label: '',
                price: 0,
                description: ''
            });
        },

        onRemoveOption(optionId) {
            this.productOptions = this.productOptions.filter((option) => option.id !== optionId);
        },

        async onSave() {
            if (!this.selectedProductId) {
                return;
            }

            this.isLoading = true;

            const customFields = { ...(this.selectedProduct?.customFields || {}) };
            customFields.advanced_product_customization_options = this.productOptions.map((option) => ({
                id: option.id,
                name: option.name || option.label,
                label: option.label || option.name,
                price: Number(option.price) || 0,
                description: option.description || ''
            }));

            const payload = {
                id: this.selectedProductId,
                customFields
            };

            try {
                await this.productRepository.save(payload, Context.api);
                if (this.selectedProduct) {
                    this.selectedProduct.customFields = customFields;
                }
                this.createNotificationSuccess({
                    message: this.$tc('advanced-product-customization.notifications.saved')
                });
            } catch (error) {
                this.createNotificationError({
                    message: this.$tc('global.notification.notificationSaveErrorMessage')
                });
            } finally {
                this.isLoading = false;
            }
        }
    }
});
