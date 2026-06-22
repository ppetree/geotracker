-- Conservative migration: create new table, copy data, rename tables atomically
-- This migration creates a new table using the target schema, copies data from the existing table (mapping columns),
-- then atomically renames the tables so the site keeps using the original table name. The old table is left as *_old
-- for verification and can be dropped after testing.

CREATE TABLE IF NOT EXISTS `#__geotracker_visitors_new` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `last_visit` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when last visited',
  `num_visits` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'number of visits',
  `geoLocation` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copy existing data. If your original table has different columns, adjust the SELECT list accordingly.
INSERT INTO `#__geotracker_visitors_new` (`id`,`last_visit`,`num_visits`,`geoLocation`)
SELECT `id`,`last_visit`,`num_visits`,`geoLocation` FROM `#__geotracker_visitors`;

-- Atomically swap tables: keep old as backup
RENAME TABLE `#__geotracker_visitors` TO `#__geotracker_visitors_old`, `#__geotracker_visitors_new` TO `#__geotracker_visitors`;
