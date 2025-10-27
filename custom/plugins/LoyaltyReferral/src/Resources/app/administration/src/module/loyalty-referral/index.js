import './page/sw-loyalty-referral-list';
import './page/sw-loyalty-referral-report';

const { Module } = Shopware;

Module.register('sw-loyalty-referral', {
    type: 'plugin',
    name: 'Loyalty Referral',
    title: 'loyalty-referral.general.mainMenuItemGeneral',
    description: 'Manage loyalty points, referral rules and reporting.',
    color: '#0b975b',
    icon: 'regular-gift',

    routes: {
        index: {
            component: 'sw-loyalty-referral-list',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index',
            },
        },
        report: {
            component: 'sw-loyalty-referral-report',
            path: 'report',
            meta: {
                parentPath: 'sw.loyalty.referral.index',
            },
        },
    },

    navigation: [{
        id: 'sw-loyalty-referral',
        label: 'loyalty-referral.general.mainMenuItemGeneral',
        color: '#0b975b',
        path: 'sw.loyalty.referral.index',
        parent: 'sw-settings',
        position: 100,
    }],
});
