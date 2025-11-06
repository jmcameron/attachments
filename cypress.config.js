const fs = require("fs");
const path = require("path");

module.exports = {
  e2e: {
    baseUrl: process.env.JOOMLA_URL,
    setupNodeEvents(on, config) {
      const db = {
        host: process.env.JOOMLA_DB_HOST,
        user: process.env.JOOMLA_DB_USER,
        password: process.env.JOOMLA_DB_PASSWORD,
        database: process.env.JOOMLA_DB_NAME,
      };
      on("task", {
        resetDatabase: () => {
          // Use mysqldump to restore from a clean backup
          const { execSync } = require("child_process");
          execSync(
            `mysql -h ${db.host} -u ${db.user} -p${db.password} ${db.database} < /tmp/clean_backup.sql`
          );
          return null;
        },
        dumpDatabase: () => {
          // Use mysqldump to create a backup of the database
          const { execSync } = require("child_process");
          execSync(
            `mysqldump -h ${db.host} -u ${db.user} -p${db.password} ${db.database} > /tmp/clean_backup.sql`
          );
          return null;
        },
        findAttachmentFile(directory) {
          const files = fs.readdirSync(directory);
          const attachmentFile = files.find(
            (file) => file.startsWith("attachments-") && file.endsWith(".zip")
          );
          return attachmentFile || null;
        },
        makePackage: () => {
          // Use mysqldump to create a backup of the database
          const { execSync } = require("child_process");
          execSync(
            "cd /app && make veryclean && make && mv attachments-*.zip /app/cypress/fixtures/"
          );
          return null;
        },
      });
    },
  },
};
