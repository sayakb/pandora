CREATE TABLE `pdr_cron` (
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pdr_programs` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext,
  `start_time` int(11) unsigned NOT NULL,
  `end_time` int(11) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pdr_session` (
  `username` varchar(255) NOT NULL DEFAULT '',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `sid` varchar(40) NOT NULL DEFAULT '',
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`),
  KEY `pdr_idx_session_key` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;