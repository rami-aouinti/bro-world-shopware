describe('Loyalty Referral account overview', () => {
    beforeEach(() => {
        cy.loginViaApi().then(() => {
            cy.visit('/account');
        });
    });

    it('shows loyalty widgets on the account page', () => {
        cy.contains('Your loyalty points').should('exist');
        cy.contains('Invite friends').should('exist');
        cy.contains('Recent loyalty activity').should('exist');
    });
});
