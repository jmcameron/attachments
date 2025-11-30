import "./common"
import "./extensions"
import "./joomla"
import "./support"
import "./user"

declare global {
  namespace Cypress {
    interface Chainable {
      dbDisableExtension(extensionName: string): Chainable<JQuery<HTMLElement>>
      adminLogin(): Chainable
    }
  }
}

export {}