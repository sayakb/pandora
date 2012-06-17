--
-- Table structure for table `pdr_cron`
--

DROP TABLE IF EXISTS `pdr_cron`;

CREATE TABLE `pdr_cron` (
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `pdr_session`
--

DROP TABLE IF EXISTS `pdr_session`;

CREATE TABLE `pdr_session` (
  `username` varchar(255) NOT NULL DEFAULT '',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `sid` varchar(40) NOT NULL DEFAULT '',
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`),
  KEY `pdr_idx_session_key` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;