declare global {
  namespace Cypress {
    interface Chainable {
      // joomla-cypress function declarations - extensions.js
      installExtensionFromFolder(path: string, type?: string): Chainable
      installExtensionFromUrl(url: string, type?: string): Chainable
      installExtensionFromFileUpload(file: FixtureData, type?: string): Chainable
      uninstallExtension(extensionName: string): Chainable
      installLanguage(languageName: string): Chainable
      enablePlugin(pluginName: string): Chainable
      setModulePosition(module: string, position?: string): Chainable
      publishModule(module: string): Chainable
      displayModuleOnAllPages(module: string): Chainable
    }
  }
}

import { extensionsCommands } from 'joomla-cypress/src/extensions'

extensionsCommands();

export {}