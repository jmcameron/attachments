declare global {
  namespace Cypress {
    interface Chainable {
      // joomla-cypress function declarations - user.js

      /**
       * Do administrator login
       *
       * @memberof cy
       * @method doAdministratorLogin
       * @param {string} user
       * @param {string} password
       * @param {boolean} useSnapshot
       * @returns Chainable
       */
      doAdministratorLogin(user: string, password: string, useSnapshot?: boolean): Chainable

      /**
       * Do administrator logout
       *
       * @memberof cy
       * @method doAdministratorLogout
       * @returns Chainable
       */
      doAdministratorLogout(): Chainable

      /**
       * Do frontend logout
       *
       * @memberof cy
       * @method doFrontendLogin
       * @param {string} user
       * @param {string} password
       * @param {boolean} useSnapshot
       * @returns Chainable
       */
      doFrontendLogin(user: string, password: string, useSnapshot?: boolean): Chainable

      /**
       * Do frontend logout
       *
       * @memberof cy
       * @method doFrontendLogout
       * @returns Chainable
       */
      doFrontendLogout(): Chainable

      /**
       * Create a user
       *
       * @memberof cy
       * @method createUser
       * @param {string} name
       * @param {string} username
       * @param {string} password
       * @param {string} email
       * @param {string} userGroup
       * @returns Chainable
       */
      createUser(name: string, username: string, password: string, email: string, userGroup?: string): Chainable
    }
  }
}

import { userCommands } from 'joomla-cypress/src/user'

userCommands();

export {}