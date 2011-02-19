CREATE TABLE IF NOT EXISTS `#__attachments`
(
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(240) NOT NULL,
    `filename_sys` TEXT NOT NULL,
    `file_type` VARCHAR(128) NOT NULL,
    `file_size` INT(11) UNSIGNED NOT NULL,
    `url` TEXT NOT NULL DEFAULT '',
    `uri_type` ENUM('file', 'url') DEFAULT 'file',
    `url_valid` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `display_name` VARCHAR(240) NOT NULL DEFAULT '',
    `description` TEXT NOT NULL DEFAULT '',
    `icon_filename` VARCHAR(60) NOT NULL,
    `uploader_id` INT(11) NOT NULL,
    `published` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `user_field_1` TEXT NOT NULL DEFAULT '',
    `user_field_2` TEXT NOT NULL DEFAULT '',
    `user_field_3` TEXT NOT NULL DEFAULT '',
    `parent_type`  VARCHAR(100) NOT NULL DEFAULT 'com_content',
    `parent_entity`  VARCHAR(100) NOT NULL DEFAULT 'default',
    `parent_id` INT(11) UNSIGNED DEFAULT NULL,
    `create_date` DATETIME DEFAULT NULL,
    `modification_date` DATETIME DEFAULT NULL,
    `download_count` INT(11) UNSIGNED DEFAULT '0',
     PRIMARY KEY (`id`)
);

