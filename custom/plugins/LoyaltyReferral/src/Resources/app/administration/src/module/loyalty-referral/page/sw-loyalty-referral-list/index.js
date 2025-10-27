import template from './sw-loyalty-referral-list.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-loyalty-referral-list', {
    template,

    inject: ['repositoryFactory'],

    mixins: [Mixin.getByName('listing')],

    data() {
        return {
            items: [],
            total: 0,
            isLoading: false,
        };
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getList();
        },

        getList() {
            this.isLoading = true;
            const repository = this.repositoryFactory.create('loyalty_balance');
            const criteria = new Shopware.Data.Criteria();
            criteria.addAssociation('customer');

            repository.search(criteria, Shopware.Context.api).then((result) => {
                this.items = result;
                this.total = result.total;
                this.isLoading = false;
            }).catch(() => {
                this.isLoading = false;
            });
        },
    },
});
