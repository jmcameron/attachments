import "../../support"

describe("Attachments package installation tests", () => {
  it("should appear in the installed extensions list after installation", () => {
    const extensionName = "Attachments";

    // Log in as administrator
    cy.adminLogin();

    // Verify that no extension with this name exists in the installed extensions list
    cy.visit("/administrator/index.php?option=com_installer&view=manage");
    cy.searchForItem(extensionName);
    cy.contains(extensionName).should("not.exist");

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

  it("should reinstall without errors", () => {
    const extensionName = "Attachments";

    // Log in as administrator
    cy.adminLogin();

    // Verify that no extension with this name exists in the installed extensions list
    // Note: The previous test already installed the extension, so the files are present
    // but the database entries were removed before this test
    cy.visit("/administrator/index.php?option=com_installer&view=manage");
    cy.searchForItem(extensionName);
    cy.contains(extensionName).should("not.exist");

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

    // Clean up by uninstalling the extension
    cy.uninstallExtension(`Package: ${extensionName}`);

    // Reinstall a second time to verify no errors occur
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
}).beforeAll(() => {
  cy.task("makePackage").then(() => {
    cy.log("Package created");
  });
});;
