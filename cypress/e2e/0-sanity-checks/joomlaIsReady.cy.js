/// <reference types="cypress" />

import * as joomlaCypress from "joomla-cypress";

joomlaCypress.registerCommands();

describe("Joomla is ready", () => {
  it("should load the Joomla admin login page", () => {
    cy.visit("/administrator/");
    cy.get("#form-login").should("be.visible");
  });

  it("should log in to the Joomla admin panel", () => {
    cy.adminLogin().visit("/administrator/");
    cy.get(".admin.com_cpanel").should("be.visible");
  });

  it("should log out from the Joomla admin panel", () => {
    cy.adminLogin().visit("/administrator/");
    cy.get(".admin.com_cpanel").should("be.visible");

    cy.doAdministratorLogout();
    cy.get("#form-login").should("be.visible");
  });
});
