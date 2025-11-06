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
    // cy.get('input[placeholder="Search Extensions"]').type(extensionName);
    cy.contains(extensionName).should("exist");
  });
});
