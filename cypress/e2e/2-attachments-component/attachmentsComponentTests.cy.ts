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
    cy.get(".item-level-2 > a > .sidebar-item-title").should("contain", "Attachments");
    cy.get(".item-level-3 > a > .sidebar-item-title").should("contain", "Attachments");
    cy.get(".item-level-3 > a > .sidebar-item-title").should("contain", "Add new attachment");
    cy.get(".item-level-3 > a > .sidebar-item-title").should("contain", "Options");
  });

  it("should be accessible from the admin menu", () => {
    cy.visit("/administrator/index.php?option=com_attachments");
    cy.get("h1").should("contain", "Attachments");
  });

  it("should have an accessible 'Add new attachment' page", () => {
    cy.visit("/administrator/index.php?option=com_attachments&task=attachment.add");
    cy.get("h1").should("contain", "Add attachment");
  });

  it("should be able to add attachments to an article through the editor", () => {
    cy.removeAllArticles();
    cy.removeAllAttachments();

    cy.dbEnableExtension("com_attachments");
    cy.dbEnableExtension("plg_attachments_plugin_framework");
    cy.dbEnableExtension("plg_system_show_attachments_in_editor");
    cy.dbEnableExtension("plg_editors-xtd_add_attachment_btn");
    cy.isExtensionInstalled("com_attachments").should("have.length", 1);
    cy.isExtensionInstalled("plg_attachments_plugin_framework").should("have.length", 1);
    cy.isExtensionInstalled("plg_system_show_attachments_in_editor").should("have.length", 1);
    cy.isExtensionInstalled("plg_editors-xtd_add_attachment_btn").should("have.length", 1);

    cy.addArticleWithAttachment();
  });

  it("should be able to add attachments to an article though the component", () => {
    cy.removeAllArticles();
    cy.removeAllAttachments();

    cy.dbEnableExtension("com_attachments");
    cy.dbEnableExtension("plg_attachments_plugin_framework");
    cy.dbEnableExtension("plg_system_show_attachments_in_editor");
    cy.isExtensionInstalled("com_attachments").should("have.length", 1);
    cy.isExtensionInstalled("plg_attachments_plugin_framework").should("have.length", 1);
    cy.isExtensionInstalled("plg_system_show_attachments_in_editor").should("have.length", 1);

    cy.addArticleWithoutAttachment().then((articleId) => {
      cy.visit("/administrator/index.php?option=com_attachments&task=attachment.add");
      cy.get("button[data-bs-target='#modal-attachment']").click();
      cy.get(".iframe")
        .iframe()
        .then($iframeBody => {
          cy.wait(1000); // Wait for the iframe to load and the article list to be populated
          cy.wrap($iframeBody).find(`a[data-id="${articleId}"]`).click();
        });
      cy.get("#upload").attachFile("test-image.png", { subjectType: "input" });
      cy.clickToolbarButton("save & close");
      cy.get(".alert-message").should("contain", "Uploaded attachment");
      cy.visit(`/administrator/index.php?option=com_content&task=article.edit&id=${articleId}`);
      cy.get(".attachmentsList").should("contain", "test-image.png");
      cy.clickToolbarButton("cancel");
    });
  });

  it("should display the configuration page", () => {
    cy.visit("/administrator/index.php?option=com_attachments&task=params.edit");
    cy.get("h1").should("contain", "Options");
  });
});