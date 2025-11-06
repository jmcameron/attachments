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
  // Disable the Joomla! Statistics plugin to avoid issues with tests
  cy.dbDisableExtension('plg_system_stats').then(() => {
    cy.log("Disabled System - Joomla! Statistics plugin");
  });
  // Disable the EOS Quickicon plugin to avoid issues with tests
  cy.dbDisableExtension('plg_quickicon_eos').then(() => {
    cy.log("Disabled EOS Quickicon plugin");
  });

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

// Disable an extension via direct database query
Cypress.Commands.add("dbDisableExtension", (extensionName) => {
  cy.log('Extension Name: ' + extensionName)
  const query = `UPDATE joom_extensions SET enabled = 0 WHERE name = '${extensionName}';`;
  return cy.query(query);
});
