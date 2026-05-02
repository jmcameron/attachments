import "../../support"

describe("Search plugin (plg_search_attachments) tests", () => {
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
    cy.searchForItem("Search - Attachments");
    cy.contains("Search - Attachments").should("exist");
  });

  it("should be in the search plugin group", () => {
    cy.visit("/administrator/index.php?option=com_plugins");
    cy.setFilter('folder', 'search');
    cy.searchForItem("Search - Attachments");
    cy.contains("Search - Attachments").should("exist");
  });
});