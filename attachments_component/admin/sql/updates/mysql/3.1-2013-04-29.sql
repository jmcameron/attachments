ALTER TABLE  `#__attachments` ADD COLUMN `url_verify` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `url_relative`;
