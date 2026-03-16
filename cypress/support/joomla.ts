export type JoomlaInstallConfig = {
	sitename: string;
	name: string;
	username: string;
	password: string;
	email: string;
	db_host: string;
	db_port?: string;
	db_type: string;
	db_user: string;
	db_password: string;
	db_name: string;
	db_prefix: string;
}

declare global {
  namespace Cypress {
    interface Chainable {
      // joomla-cypress function declarations - joomla.js
      installJoomla(config: JoomlaInstallConfig): Chainable
      cancelTour(): Chainable
      disableStatistics(): Chainable
      setErrorReportingToDevelopment(): Chainable
      installJoomlaMultilingualSite(config: JoomlaInstallConfig, languages?: string[]): Chainable
    }
  }
}

import { joomlaCommands } from 'joomla-cypress/src/joomla'

joomlaCommands();

export {}