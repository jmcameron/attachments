CREATE TABLE IF NOT EXISTS `#__attachments`
(
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(80) NOT NULL,
    `filename_sys` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(128) NOT NULL,
    `file_size` INT(11) UNSIGNED NOT NULL,
    `url` VARCHAR(1024) NOT NULL DEFAULT '',
    `uri_type` ENUM('file', 'url') DEFAULT 'file',
    `url_valid` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `url_relative` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    `display_name` VARCHAR(80) NOT NULL DEFAULT '',
    `description` VARCHAR(255) NOT NULL DEFAULT '',
    `icon_filename` VARCHAR(20) NOT NULL,
    `access` INT(11) NOT NULL DEFAULT '1',
    `state` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
    `user_field_1` VARCHAR(255) NOT NULL DEFAULT '',
    `user_field_2` VARCHAR(255) NOT NULL DEFAULT '',
    `user_field_3` VARCHAR(255) NOT NULL DEFAULT '',
    `parent_type`  VARCHAR(100) NOT NULL DEFAULT 'com_content',
    `parent_entity`  VARCHAR(100) NOT NULL DEFAULT 'article',
    `parent_id` INT(11) UNSIGNED DEFAULT NULL,
    `created` DATETIME DEFAULT NULL,
    `created_by` INT(11) NOT NULL,
    `modified` DATETIME DEFAULT NULL,
    `modified_by` INT(11) NOT NULL,
    `download_count` INT(11) UNSIGNED DEFAULT '0',

     PRIMARY KEY (`id`)

) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;
