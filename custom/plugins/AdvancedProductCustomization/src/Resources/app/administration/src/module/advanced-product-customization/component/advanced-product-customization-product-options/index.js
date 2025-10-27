import template from './advanced-product-customization-product-options.html.twig';
import './advanced-product-customization-product-options.scss';

const { Component } = Shopware;

Component.register('advanced-product-customization-product-options', {
    template,
    emits: ['add-option', 'remove-option', 'update:value'],
    props: {
        value: {
            type: Array,
            required: true
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },

    computed: {
        options: {
            get() {
                return this.value;
            },
            set(options) {
                this.$emit('update:value', options);
            }
        }
    },

    methods: {
        addOption() {
            this.$emit('add-option');
        },

        removeOption(option) {
            this.$emit('remove-option', option.id);
        }
    }
});
