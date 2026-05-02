import "../../support"

describe("Quickicon plugin (plg_quickicon_attachments) tests", () => {
  before(() => {
    cy.task("makePackage").then(() => {
      cy.log("Package created");
    });
  });

  beforeEach(() => {
    cy.adminLogin();
    cy.installAttachmentsIfNeeded();
  });

  it("should appear in the extensions list", () => {
    cy.visit("/administrator/index.php?option=com_installer&view=manage");
    cy.searchForItem("Quick Icon - Attachments");
    cy.contains("Quick Icon - Attachments").should("exist");
  });

  it("should be in the quickicon plugin group", () => {
    cy.visit("/administrator/index.php?option=com_plugins");
    cy.setFilter('folder', 'quickicon');
    cy.searchForItem("Quick Icon - Attachments");
    cy.contains("Quick Icon - Attachments").should("exist");
  });

  it("should be able to enable the plugin", () => {
    cy.visit("/administrator/index.php?option=com_plugins");
    cy.searchForItem("Quick Icon - Attachments");

    cy.isExtensionInstalled('plg_quickicon_attachments').then((result) => {
      if (Array.isArray(result) && result.length > 0) {
        cy.get("#cb0").click();
        cy.log(`Plugin is already enabled.`);
        // cy.clickToolbarButton("disable");
        cy.get("#toolbar-unpublish button").click();
        cy.checkForSystemMessage("disabled");
      } else {
        cy.log(`Plugin is currently disabled, enabling it.`);
      }
    });

    cy.get("#cb0").click();
    cy.clickToolbarButton("enable");
    cy.checkForSystemMessage("enabled");
  });
});