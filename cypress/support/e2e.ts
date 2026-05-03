// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import "./commands";

before(() => {
  // Disable the Joomla! Statistics plugin to avoid issues with tests
  cy.dbDisableExtension('plg_system_stats').then(() => {
    cy.log("Disabled System - Joomla! Statistics plugin");
  });
  // Disable the EOS Quickicon plugin to avoid issues with tests
  cy.dbDisableExtension('plg_quickicon_eos').then(() => {
    cy.log("Disabled EOS Quickicon plugin");
  });

  // Dump the database to create a clean backup before any test runs
  // cy.task("dumpDatabase");
});

beforeEach(() => {
  // Reset the database before each test
  // cy.task("resetDatabase");
  Cypress.session.clearAllSavedSessions();
});

Cypress.Commands.add("adminLogin", () => {
  return cy.doAdministratorLogin(
    Cypress.env("JOOMLA_ADMIN_USERNAME"),
    Cypress.env("JOOMLA_ADMIN_PASSWORD")
  );
});

// Disable an extension via direct database query
Cypress.Commands.add("dbDisableExtension", (extensionName) => {
  cy.log('Extension Name: ' + extensionName)
  const query = `UPDATE joom_extensions SET enabled = 0 WHERE name = ?;`;
  return cy.query(query, [extensionName]);
});

Cypress.Commands.add("dbEnableExtension", (extensionName) => {
  cy.log('Extension Name: ' + extensionName)
  const query = `UPDATE joom_extensions SET enabled = 1 WHERE name = ?;`;
  return cy.query(query, [extensionName]);
});

Cypress.Commands.add("isExtensionInstalled", (extensionName) => {
  cy.log('is extension ' + extensionName + ' installed?')
  const query = 'SELECT * FROM joom_extensions WHERE name = ? AND enabled = 1';
  return cy.query(query, [extensionName]);
});

Cypress.Commands.add("installAttachmentsIfNeeded", () => {
    cy.visit("/administrator/index.php?option=com_installer&view=manage");
    cy.searchForItem("Attachments");
    cy.get("body").then(($body) => {
      if ($body.find('tbody > tr').length > 0 && $body.text().includes("Attachments")) {
        cy.log("Attachments already installed");
      } else {
        cy.task("findAttachmentFile", "/app/cypress/fixtures").then((filename) => {
          if (filename) {
            cy.log(`Found file: ${filename}`);
            cy.installExtensionFromFileUpload(filename);
          }
        });
      }
    });
});

Cypress.Commands.add("removeAllArticles", () => {
  const query = 'DELETE FROM joom_content';
  return cy.query(query);
});

Cypress.Commands.add("removeAllAttachments", () => {
  cy.task("clearAttachmentsDir");

  const query = 'DELETE FROM joom_attachments';
  return cy.query(query);
});

Cypress.Commands.add("showAddAttachmentDialogThroughEditor", () => {
  cy.get('body').then(($body) => {
    if ($body.find('button.tox-tbtn').length > 0) {
      // Joomla 4.4+ with TinyMCE 6
      cy.log("Found editor toolbar button");
      cy.get('button.tox-tbtn').contains("CMS Content").click({
        waitForAnimations: false,
      });
      cy.get('div[title="Add attachment"]').click();
    } else {
      throw new Error("Editor toolbar button not found");
    }
  });
});

Cypress.Commands.add("setEditorContent", (content: string) => {
  cy.window().then((win) => {
    if (win.tinyMCE) {
      win.tinyMCE.activeEditor.setContent(content);
    } else {
      throw new Error("TinyMCE not found");
    }
  });
});

Cypress.Commands.add("addArticleWithAttachment", (title: string = "Test Article for Attachments", content: string = "This is a test article to verify attachments functionality.", attachmentPath: string = "test-image.png") => {
    // Create a new article
    cy.visit("/administrator/index.php?option=com_content&task=article.add");
    cy.get("#jform_title").type(title);
    cy.setEditorContent(content);

    // Go to the attachments tab and add an attachment
    cy.showAddAttachmentDialogThroughEditor();
    cy.wait(1000); // Wait for the upload to complete and the page to update
    cy.get(".iframe").then(($iframe) => {
      const $body = $iframe.contents().find("body");
      cy.wrap($body)
        .find("#upload")
        .attachFile(attachmentPath, { subjectType: "input" });
      cy.wrap($body)
        .find("form.attachmentsBackend")
        .contains("button", "Upload")
        .click();
    });

    // Verify the attachment is listed
    cy.get(".attachmentsList").should("contain", "test-image.png");

    cy.clickToolbarButton("save");
    cy.get(".alert-message").should("contain", "Article saved");

    // Verify the attachment is still listed
    cy.get(".attachmentsList").should("contain", "test-image.png");

    cy.clickToolbarButton("cancel");

    cy.query("SELECT id FROM joom_content order by id desc limit 1").then((result) => {
      const articleId = result[0].id;
      cy.wrap(articleId).as("articleId");
    });

    return cy.get("@articleId");
  });

Cypress.Commands.add("addArticleWithoutAttachment", (title: string = "Test Article for Attachments", content: string = "This is a test article to verify attachments functionality.") => {
    // Create a new article
    cy.visit("/administrator/index.php?option=com_content&task=article.add");
    cy.get("#jform_title").type(title);
    cy.setEditorContent(content);

    cy.clickToolbarButton("save & close");
    cy.get(".alert-message").should("contain", "Article saved");
    cy.query("SELECT id FROM joom_content order by id desc limit 1").then((result) => {
      const articleId = result[0].id;
      cy.wrap(articleId).as("articleId");
    });

    return cy.get("@articleId");
});

// Fix for "Cannot read properties of undefined (reading 'addEventListener')" error caused by Joomla's toolbar in 4.4
Cypress.on('uncaught:exception', (err, runnable) => {
  // returning false prevents Cypress from failing the test
  if (err.message.includes('Cannot read properties of undefined (reading \'addEventListener\')')) {
    console.log('Caught expected exception:', err.message);
    return false
  }
})