import "../../support"

describe("Finder plugin (plg_finder_attachments) tests", () => {
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
    cy.searchForItem("Smart Search - Attachments");
    cy.contains("Smart Search - Attachments").should("exist");
  });

  it("should be in the finder plugin group", () => {
    cy.visit("/administrator/index.php?option=com_plugins");
    cy.setFilter('folder', 'finder');
    cy.searchForItem("Smart Search - Attachments");
    cy.contains("Smart Search - Attachments").should("exist");
  });
});