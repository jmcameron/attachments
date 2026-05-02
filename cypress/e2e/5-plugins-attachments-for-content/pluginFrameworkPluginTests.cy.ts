import "../../support"

describe("Plugin Framework plugin (plg_attachments_plugin_framework) tests", () => {
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
    cy.searchForItem("Attachments - Plugin Framework");
    cy.contains("Attachments - Plugin Framework").should("exist");
  });

  it("should be in the attachments plugin group", () => {
    cy.visit("/administrator/index.php?option=com_plugins");
    cy.setFilter('folder', 'attachments');
    cy.searchForItem("Attachments - Plugin Framework");
    cy.contains("Attachments - Plugin Framework").should("exist");
  });
});