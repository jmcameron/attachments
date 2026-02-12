declare global {
  namespace Cypress {
    interface Chainable {
      // joomla-cypress function declarations - user.js

      /**
       * Wait for iframe to load, and call callback
       *
       * Some hints taken and adapted from:
       * https://gitlab.com/kgroat/cypress-iframe/-/blob/master/src/index.ts
       */
      iframe($iframes: HTMLIFrameElement[]): Chainable
    }
  }
}

import { commonCommands } from 'joomla-cypress/src/common'

commonCommands();

export {}