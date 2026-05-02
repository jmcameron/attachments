import "../../support"

describe("Show Attachments in Editor plugin (plg_system_show_attachments_in_editor) tests", () => {
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
    cy.searchForItem("System - Show attachments in editor");
    cy.contains("System - Show attachments in editor").should("exist");
  });

  it("should be in the system plugin group", () => {
    cy.visit("/administrator/index.php?option=com_plugins");
    cy.setFilter('folder', 'system');
    cy.searchForItem("System - Show attachments in editor");
    cy.contains("System - Show attachments in editor").should("exist");
  });
});