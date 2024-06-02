CREATE TABLE IF NOT EXISTS `#__geotracker_visitors` ( 
  `id` int(2) UNSIGNED NOT NULL auto_increment,
  `last_visit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'when last visited',
  `num_visits` int UNSIGNED NOT NULL COMMENT 'number of visits',
  `geoLocation` varchar(255) NOT NULL DEFAULT ''
  PRIMARY KEY  (`id`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
