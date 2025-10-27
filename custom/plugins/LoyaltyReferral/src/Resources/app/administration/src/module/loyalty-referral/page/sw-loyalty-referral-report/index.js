import template from './sw-loyalty-referral-report.html.twig';

const { Component } = Shopware;

Component.register('sw-loyalty-referral-report', {
    template,

    inject: ['repositoryFactory'],

    data() {
        return {
            isLoading: false,
            transactions: [],
        };
    },

    created() {
        this.getTransactions();
    },

    methods: {
        getTransactions() {
            this.isLoading = true;
            const repository = this.repositoryFactory.create('loyalty_transaction');
            const criteria = new Shopware.Data.Criteria();
            criteria.addAssociation('balance.customer');
            criteria.setLimit(50);
            criteria.addSorting(Shopware.Data.Criteria.sort('createdAt', 'DESC'));

            repository.search(criteria, Shopware.Context.api).then((result) => {
                this.transactions = result;
                this.isLoading = false;
            }).catch(() => {
                this.isLoading = false;
            });
        },
    },
});
