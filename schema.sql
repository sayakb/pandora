CREATE TABLE `pdr_cron` (
  `timestamp` int(11) unsigned NOT NULL DEFAULT 0,
  `locked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `pdr_cron` (
  `timestamp`,
  `locked`
) VALUES (0, 0);

CREATE TABLE `pdr_programs` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext DEFAULT '',
  `start_time` int(11) unsigned NOT NULL,
  `end_time` int(11) unsigned NOT NULL,
  `dl_student` int(11) unsigned NOT NULL,
  `dl_mentor` int(11) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pdr_projects` (
  `id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext,
  `program_id` mediumint(6) unsigned NOT NULL,
  `is_accepted` tinyint(1) NOT NULL DEFAULT -1,
  `is_complete` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`program_id`) REFERENCES `pdr_programs`(`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pdr_participants` (
  `id` mediumint(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `project_id` mediumint(10) NOT NULL,
  `program_id` mediumint(6) unsigned NOT NULL,
  `role` char(1) NOT NULL DEFAULT 's',
  `passed` tinyint(1) DEFAULT -1,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`project_id`) REFERENCES `pdr_projects`(`id`),
  FOREIGN KEY (`program_id`) REFERENCES `pdr_programs`(`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pdr_roles` (
  `id` mediumint(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `program_id` mediumint(6) unsigned NOT NULL,
  `role` char(1) NOT NULL DEFAULT 's',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`program_id`) REFERENCES `pdr_programs`(`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pdr_session` (
  `username` varchar(255) NOT NULL DEFAULT '',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `sid` varchar(40) NOT NULL DEFAULT '',
  `timestamp` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pdr_bans` (
  `username` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pdr_queue` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `program_id` mediumint(6) unsigned NOT NULL,
  `deadline` tinyint(1) DEFAULT 0,
  `complete` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`program_id`) REFERENCES `pdr_programs`(`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;