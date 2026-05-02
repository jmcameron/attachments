import "../../support"

describe("Add Attachment Button plugin (plg_editors-xtd_add_attachment_btn) tests", () => {
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
    cy.searchForItem("Editor Button - Add Attachment");
    cy.contains("Editor Button - Add Attachment").should("exist");
  });

  it("should be in the editors-xtd plugin group", () => {
    cy.visit("/administrator/index.php?option=com_plugins");
    cy.setFilter('folder', 'editors-xtd');
    cy.searchForItem("Editor Button - Add Attachment");
    cy.contains("Editor Button - Add Attachment").should("exist");
  });
});