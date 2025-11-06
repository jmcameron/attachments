/// <reference types="cypress" />

import * as joomlaCypress from "joomla-cypress";

joomlaCypress.registerCommands();

describe("Attachments package installation tests", () => {
  it("should appear in the installed extensions list after installation", () => {
    const extensionName = "Attachments";

    cy.task("makePackage").then(() => {
      cy.log("Package created");
    });
    // Log in as administrator
    cy.adminLogin().visit("/administrator/");

    // Install the Attachments extension from a local file
    cy.task("findAttachmentFile", "/app/cypress/fixtures").then((filename) => {
      if (filename) {
        cy.log(`Found file: ${filename}`);
        // Use the filename for further operations
        cy.installExtensionFromFileUpload(filename);
      } else {
        throw new Error("Attachment file not found");
      }
    });

    // Verify that the extension appears in the installed extensions list
    cy.visit("/administrator/index.php?option=com_installer&view=manage");
    cy.searchForItem(extensionName);
    cy.contains(extensionName).should("exist");
    // Verify that there are 11 items listed (Attachments component, 9 plugins and 1 package)
    cy.get('tbody > tr').should('have.length', 11);
    cy.get('tbody > tr > :nth-child(5)').filter(':contains("Component")').should('have.length', 1);
    cy.get('tbody > tr > :nth-child(5)').filter(':contains("Package")').should('have.length', 1);
    cy.get('tbody > tr > :nth-child(5)').filter(':contains("Plugin")').should('have.length', 9);
  });
});
