DROP TABLE IF EXISTS `#__jdefender_log`;
DROP TABLE IF EXISTS `#__jdefender_filesystem`;
DROP TABLE IF EXISTS `#__jdefender_vars`;
DROP TABLE IF EXISTS `#__jdefender_block_list`;
DROP TABLE IF EXISTS `#__jdefender_flood_monitor`;

CREATE TABLE  `#__jdefender_block_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` varchar(255) DEFAULT NULL,
  `published` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
);


CREATE TABLE  `#__jdefender_filesystem` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `fullpath` text NOT NULL,
  `permission` int(10) unsigned NOT NULL DEFAULT '0',
  `ctime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(45) NOT NULL DEFAULT '',
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `hash_md` varchar(32) NOT NULL DEFAULT '',
  `scandate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `contents` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `path_index` (`fullpath`(255))
);

CREATE TABLE  `#__jdefender_flood_monitor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL DEFAULT '',
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
);

CREATE TABLE  `#__jdefender_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(50) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `url` text NOT NULL,
  `post` text NOT NULL,
  `cook` text NOT NULL,
  `referer` text NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `issue` text NOT NULL,
  `user_agent` text,
  `extension` varchar(255) NOT NULL,
  `total` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_index` (`user_id`),
  KEY `url_index` (`url`(255))
);

CREATE TABLE  `#__jdefender_vars` (
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`(150), `type`(150))
);