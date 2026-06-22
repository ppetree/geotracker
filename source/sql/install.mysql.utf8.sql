CREATE TABLE IF NOT EXISTS `#__geotracker_visitors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `last_visit` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when last visited',
  `num_visits` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'number of visits',
  `geoLocation` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
