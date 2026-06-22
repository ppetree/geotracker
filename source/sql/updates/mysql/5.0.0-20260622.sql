-- Upgrade script: migrate table engine/charset and id size if necessary
-- This script will alter the existing table to use InnoDB and utf8mb4 and ensure id is int(10) unsigned AUTO_INCREMENT
-- It is designed to be safe when run on a table that already matches the target schema.

ALTER TABLE `#__geotracker_visitors`
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    ENGINE=InnoDB;

-- Ensure id column is large enough
ALTER TABLE `#__geotracker_visitors`
    MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

-- Ensure num_visits has a default
ALTER TABLE `#__geotracker_visitors`
    MODIFY `num_visits` INT UNSIGNED NOT NULL DEFAULT 1;
