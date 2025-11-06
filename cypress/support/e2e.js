// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import "./commands";

before(() => {
  // Dump the database to create a clean backup before any test runs
  cy.task("dumpDatabase");
});

beforeEach(() => {
  // Reset the database before each test
  cy.task("resetDatabase");
  Cypress.session.clearAllSavedSessions();
});

Cypress.Commands.add("adminLogin", () => {
  return cy.doAdministratorLogin(
    Cypress.env("JOOMLA_ADMIN_USERNAME"),
    Cypress.env("JOOMLA_ADMIN_PASSWORD")
  );
});
