const fs = require("fs");
const mysql = require('cypress-mysql');

const db = {
  host: process.env.JOOMLA_DB_HOST,
  user: process.env.JOOMLA_DB_USER,
  password: process.env.JOOMLA_DB_PASSWORD,
  database: process.env.JOOMLA_DB_NAME,
};

module.exports = {
  e2e: {
    baseUrl: process.env.JOOMLA_URL,
    env: {
      db: db,
    },
    setupNodeEvents(on, config) {
      mysql.configurePlugin(on);

      on("task", {
        // Use mysql client to restore from a clean backup
        resetDatabase: () => {
          const { execSync } = require("child_process");
          execSync(
            `mysql -h ${db.host} -u ${db.user} -p${db.password} ${db.database} < /tmp/clean_backup.sql`
          );
          return null;
        },

        // Use mysqldump to create a backup of the database
        dumpDatabase: () => {
          const { execSync } = require("child_process");
          execSync(
            `mysqldump -h ${db.host} -u ${db.user} -p${db.password} ${db.database} > /tmp/clean_backup.sql`
          );
          return null;
        },

        // Find the built attachment file in the specified directory
        findAttachmentFile(directory) {
          const files = fs.readdirSync(directory);
          const attachmentFile = files.find(
            (file) => file.startsWith("attachments-") && file.endsWith(".zip")
          );
          return attachmentFile || null;
        },

        // Run make to build the Joomla extension package
        makePackage: () => {
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
