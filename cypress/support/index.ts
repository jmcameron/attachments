import "./common"
import "./extensions"
import "./joomla"
import "./support"
import "./user"

declare global {
  namespace Cypress {
    interface Chainable {
      dbDisableExtension(extensionName: string): Chainable
      isExtensionInstalled(extensionName: string): Chainable
      installAttachmentsIfNeeded(): Chainable
      adminLogin(): Chainable
    }
  }
}

export {}