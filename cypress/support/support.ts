type JoomlaToolbarButton = "new" | 
  "enable" | 
  "publish" | 
  "unpublish" | 
  "archive" | 
  "check-in" | 
  "batch" | 
  "rebuild" | 
  "trash" | 
  "save" |
  "save & close" |
  "save & new" |
  "cancel" |
  "options" |
  "empty trash" |
  "delete" |
  "feature" |
  "unfeature" |
  "action" |
  "transition" |
  "versions"

declare global {
  namespace Cypress {
    interface Chainable {
      // joomla-cypress function declarations - support.js

      /**
       * Clicks on a button in the toolbar
       *
       * @memberof cy
       * @method clickToolbarButton
       * @param {string} button
       * @param {string} subselector
       * @returns Chainable
       */
      clickToolbarButton(button: JoomlaToolbarButton, subselector?): Chainable

      /**
       * Check for PHP notices and warnings in the HTML page source.
       * This command looks for keywords such as 'Deprecated' in bold followed by a colon.
       * If such a styled keyword is found, the test fails with the PHP problem message.
       *
       * Looking for:
       * - <b>Warning</b>:
       * - <b>Deprecated</b>:
       * - <b>Notice</b>:
       * - <b>Strict standards</b>:
       *
       * @memberof Cypress.Commands
       * @method checkForPhpNoticesOrWarnings
       * @returns {void} - It does not return any value, but it is chainable with other Cypress commands.
       */
      checkForPhpNoticesOrWarnings(): Chainable

      /**
       * @memberof cy
       * @method checkForSystemMessage
       * @param {string} contain - what we are looking for, e.g. 'published'
       * @returns Chainable
       */
      checkForSystemMessage(contain: string): Chainable

      /**
       * Search for an item
       * TODO: deletes search field doesn't make sense to me in this context; RD)
       *
       * @memberof cy
       * @method searchForItem
       * @param {string} name
       * @returns Chainable
       */
      searchForItem(name?: string): Chainable

      /**
       * set filter on list view
       *
       * @memberof cy
       * @method setFilter
       * @param {string} name
       * @param {string} value
       * @returns Chainable
       */
      setFilter(name: string, value: string): Chainable

      /**
       * Check all filtered results
       *
       * @memberof cy
       * @method checkAllResults
       * @returns Chainable
       */
      checkAllResults(): Chainable

      /**
       * Custom Cypress command to create a menu item in Joomla.
       *
       * @memberof Cypress.Commands
       * @method createMenuItem
       * @param {string} menuTitle – The title of the menu item to be created.
       * @param {string} menuCategory – The category of the menu item.
       * @param {string} menuItem – The used menu item type (e.g. 'Articles').
       * @param {string} [menu='Main Menu'] – The used menu item destination (e.g. 'Featured Articles').
       * @param {string} [language='All'] - Menu item language as name (e.g. 'Czech (Čeština)') or tag (e.g. 'cs-CZ').
       *
       * The 'language' parameter is only used for multilingual websites where the language selection is visible.
       * 
       * @returns {void} - It does not return any value, but it is chainable with other Cypress commands.
       */
      createMenuItem(menuTitle: string, menuCategory: string, menuItem: string, menu?: string, language?: string): Chainable

      /**
       * @memberof cy
       * @method createCategory
       * @param {string} title
       * @param {string} extension - Default com_content
       * @returns Chainable
       */
      createCategory(title: string, extension?: string): Chainable

      /**
       * Selects an option in a fancy select field
       *
       * @memberof cy
       * @method selectOptionInFancySelect
       * @param {string} selectId - The name of the field like #jform_countries
       * @param {string} option - The name of the value like 'Germany'
       * @returns Chainable
       */
      selectOptionInFancySelect(selectId: string, option: string): Chainable

      /**
       * Toggles a switch field
       *
       * @memberof cy
       * @method toggleSwitch
       * @param {string} fieldName - The name of the field like 'Published
       * @param {string} valueName - The name of the value like 'Yes'
       * @returns Chainable
       */
      toggleSwitch(fieldName: string, valueName: string): Chainable
    }
  }
}

import { supportCommands } from 'joomla-cypress/src/support'

supportCommands();

export {}