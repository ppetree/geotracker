ALTER TABLE `#__geotracker_visitors`
  ADD COLUMN `last_visit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when last visited' AFTER `id`,
  ADD COLUMN `num_visits` int UNSIGNED NOT NULL COMMENT 'number of visits' AFTER `last_visit`;
