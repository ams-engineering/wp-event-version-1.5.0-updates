
describe('Main Test', () => {
    Cypress.Cookies.defaults({
        preserve: /wordpress_.*/
    })

    it('Login', () => {
        cy.visit('https://securedevserver.com/wpevents/wp-admin/')
        cy.get('#user_login').type('admin')
        cy.get('#user_pass').type('#9LEshW%Ir')
        cy.get('#rememberme').click()
        cy.get('#loginform').submit()
    })

    it(' Create an event', () => {
        
    })

})
