/// <reference types="cypress" />

const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD = 'shopware';

function authenticateViaApi() {
    return cy.request({
        method: 'POST',
        url: '/api/oauth/token',
        body: {
            grant_type: 'password',
            client_id: 'administration',
            scopes: 'write',
            username: ADMIN_USERNAME,
            password: ADMIN_PASSWORD
        }
    }).then((response) => response.body.access_token);
}

function createCustomizedProduct(accessToken) {
    const productNumber = `APC-${Date.now()}`;

    return cy.request({
        method: 'POST',
        url: '/api/_action/sync',
        headers: {
            Authorization: `Bearer ${accessToken}`
        },
        body: {
            'product': {
                entity: 'product',
                action: 'upsert',
                payload: [
                    {
                        productNumber,
                        name: 'Advanced Custom Product',
                        description: 'Product used for advanced customization tests.',
                        stock: 100,
                        price: [
                            {
                                currencyId: 'b7d2554b0ce847cd82f3ac9bd1c0dfca',
                                gross: 99.0,
                                net: 83.19327731,
                                linked: false
                            }
                        ],
                        tax: {
                            name: 'Standard rate',
                            taxRate: 19
                        },
                        visibilities: [
                            {
                                salesChannelId: '98432def39fc4624b33213a56b8c944d',
                                visibility: 30
                            }
                        ],
                        active: true,
                        customFields: {
                            advanced_product_customization_options: [
                                {
                                    id: 'engraving',
                                    title: 'Engraving',
                                    description: 'Choose your engraving style',
                                    options: [
                                        { id: 'engraving-script', label: 'Script engraving', price: 15 },
                                        { id: 'engraving-block', label: 'Block engraving', price: 10 }
                                    ]
                                },
                                {
                                    id: 'packaging',
                                    title: 'Gift packaging',
                                    description: 'Select a packaging option',
                                    options: [
                                        { id: 'gift-basic', label: 'Basic wrapping', price: 3 },
                                        { id: 'gift-premium', label: 'Premium box', price: 12 }
                                    ]
                                }
                            ]
                        }
                    }
                ]
            }
        }
    }).then(() => productNumber);
}

function fetchProductDetailSlug(productNumber) {
    return cy.request({
        method: 'POST',
        url: '/store-api/product',
        body: {
            ids: [],
            includes: {
                product: ['id', 'productNumber', 'translated', 'seoUrls']
            },
            filter: [
                {
                    field: 'productNumber',
                    type: 'equals',
                    value: productNumber
                }
            ]
        }
    }).then((response) => {
        const product = response.body.elements[0];
        const seoUrl = product.seoUrls && product.seoUrls.length ? product.seoUrls[0].seoPathInfo : null;

        return seoUrl ? `/${seoUrl}` : `/detail/${product.id}`;
    });
}

describe('Advanced product customization', () => {
    let productNumber;
    let storefrontUrl;

    before(() => {
        return authenticateViaApi()
            .then(createCustomizedProduct)
            .then((createdProductNumber) => {
                productNumber = createdProductNumber;
                return fetchProductDetailSlug(productNumber);
            })
            .then((url) => {
                storefrontUrl = url;
            });
    });

    it('allows configuring a product and creating an order', () => {
        cy.visit(storefrontUrl);

        cy.get('[data-advanced-product-customization]').should('be.visible');
        cy.get('[data-customization-step]').first().within(() => {
            cy.contains('Script engraving').click();
            cy.contains('button', 'Next').click();
        });

        cy.get('[data-customization-step]').eq(1).within(() => {
            cy.contains('Premium box').click();
            cy.contains('button', 'Finish').click();
        });

        cy.get('[data-customization-input]').invoke('val').then((value) => {
            const parsed = JSON.parse(value);
            expect(parsed.options).to.have.length(2);
        });

        cy.get('form[action="/checkout/line-item/add"]').submit();

        cy.get('.offcanvas').should('be.visible');
        cy.get('.offcanvas').contains('Advanced Custom Product');
        cy.get('.offcanvas').contains('Script engraving');
        cy.get('.offcanvas').contains('Premium box');

        cy.contains(/checkout/i).click();
        cy.contains(/guest/i).click();

        cy.get('input[name="email"]').type('advanced-custom@example.com');
        cy.get('input[name="shippingAddress[firstName]"]').type('Advanced');
        cy.get('input[name="shippingAddress[lastName]"]').type('Customization');
        cy.get('input[name="shippingAddress[street]"]').type('Customization Street 1');
        cy.get('input[name="shippingAddress[zipcode]"]').type('12345');
        cy.get('input[name="shippingAddress[city]"]').type('Shopware City');
        cy.get('select[name="shippingAddress[countryId]"]').select(1);
        cy.get('input[name="acceptTerms"]').check({ force: true });
        cy.contains(/submit order/i).click();

        cy.contains(/thank you/i).should('be.visible');
    });
});
