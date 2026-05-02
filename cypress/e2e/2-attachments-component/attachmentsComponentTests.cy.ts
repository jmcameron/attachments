import "../../support"

describe("Attachments component tests", () => {
  before(() => {
    cy.task("makePackage").then(() => {
      cy.log("Package created");
    });
  });

  beforeEach(() => {
    cy.adminLogin();
    cy.installAttachmentsIfNeeded();
  });

  it("should install and display in the admin menu", () => {
    cy.visit("/administrator/");
    cy.get(".sidebar-item-title").should("contain", "Attachments");
  });

  it("should be accessible from the admin menu", () => {
    cy.visit("/administrator/index.php?option=com_attachments");
    cy.get("h1").should("contain", "Attachments");
  });

  it("should display the configuration page", () => {
    cy.visit("/administrator/index.php?option=com_attachments&task=params.edit");
    cy.get("h1").should("contain", "Options");
  });
});