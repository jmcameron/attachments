import "../../support"

describe("Attachments for Content plugin (plg_attachments_attachments_for_content) tests", () => {
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
    cy.searchForItem("Attachments for Content");
    cy.contains("Attachments for Content").should("exist");
  });

  it("should be in the attachments plugin group", () => {
    cy.visit("/administrator/index.php?option=com_plugins");
    cy.setFilter('folder', 'attachments');
    cy.searchForItem("Attachments for Content");
    cy.contains("Attachments for Content").should("exist");
  });
});