describe('Smart Inventory Alerts storefront form', () => {
    it('allows customers to subscribe for back-in-stock notifications', () => {
        cy.visit('/');
        cy.get('a[href*="/detail/"]').first().click();
        cy.get('[data-smart-inventory-alert-form]').as('alertForm').should('be.visible');

        cy.intercept('POST', '/smart-inventory-alerts/register').as('registerAlert');

        cy.get('@alertForm').within(() => {
            cy.get('input[name="email"]').type('cypress@example.com');
            cy.get('input[name="threshold"]').clear().type('3');
            cy.root().submit();
        });

        cy.wait('@registerAlert').its('response.statusCode').should('eq', 200);
        cy.get('.smart-inventory-alert__feedback').should('be.visible');
    });
});

describe('Smart Inventory Alerts administration dashboard', () => {
    beforeEach(() => {
        cy.loginViaApi();
    });

    it('shows low stock, slow rotation and forecast cards', () => {
        cy.visit('/admin#/smart-inventory-alerts/index');
        cy.get('.smart-inventory-alerts-dashboard').should('exist');
        cy.get('.smart-inventory-alerts-dashboard .sw-card').should('have.length', 3);
    });
});
