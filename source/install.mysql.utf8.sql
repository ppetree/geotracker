CREATE TABLE IF NOT EXISTS `#__geotracker_visitors`  ( 
						`id` int(11) NOT NULL auto_increment, 
						`geoLocation` varchar(255) NOT NULL default '', 
						PRIMARY KEY  (`id`) 
						) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
