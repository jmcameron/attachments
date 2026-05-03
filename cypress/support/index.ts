import "./common"
import "./extensions"
import "./joomla"
import "./support"
import "./user"

declare global {
  namespace Cypress {
    interface Chainable {
      dbDisableExtension(extensionName: string): Chainable
      dbEnableExtension(extensionName: string): Chainable
      isExtensionInstalled(extensionName: string): Chainable
      installAttachmentsIfNeeded(): Chainable
      removeAllArticles(): Chainable
      removeAllAttachments(): Chainable
      setEditorContent(content: string): Chainable
      showAddAttachmentDialogThroughEditor(): Chainable
      adminLogin(): Chainable
      addArticleWithAttachment(title?: string, content?: string, attachmentPath?: string): Chainable
      addArticleWithoutAttachment(title?: string, content?: string): Chainable
    }
  }
}

export {}