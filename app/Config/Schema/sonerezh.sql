

DROP TABLE IF EXISTS `sonerezh`.`playlist_memberships`;
DROP TABLE IF EXISTS `sonerezh`.`playlists`;
DROP TABLE IF EXISTS `sonerezh`.`settings`;
DROP TABLE IF EXISTS `sonerezh`.`songs`;
DROP TABLE IF EXISTS `sonerezh`.`users`;


CREATE TABLE `sonerezh`.`playlist_memberships` (
	`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	`playlist_id` int(8) UNSIGNED NOT NULL,
	`song_id` int(8) UNSIGNED NOT NULL,
	`sort` int(8) UNSIGNED NOT NULL,	PRIMARY KEY  (`id`)) 	DEFAULT CHARSET=utf8,
	COLLATE=utf8_general_ci,
	ENGINE=InnoDB;

CREATE TABLE `sonerezh`.`playlists` (
	`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`created` datetime NOT NULL,
	`modified` datetime NOT NULL,
	`user_id` int(5) UNSIGNED NOT NULL,	PRIMARY KEY  (`id`)) 	DEFAULT CHARSET=utf8,
	COLLATE=utf8_general_ci,
	ENGINE=InnoDB;

CREATE TABLE `sonerezh`.`settings` (
	`id` int(3) UNSIGNED NOT NULL AUTO_INCREMENT,
	`rootpath` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
	`enable_auto_conv` tinyint(1) DEFAULT '0' NOT NULL,
	`convert_from` varchar(25) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'aac,flac' NOT NULL,
	`convert_to` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'mp3' NOT NULL,
	`quality` int(3) UNSIGNED DEFAULT 256 NOT NULL,
	`enable_mail_notification` tinyint(1) DEFAULT '0' NOT NULL,	PRIMARY KEY  (`id`)) 	DEFAULT CHARSET=utf8,
	COLLATE=utf8_general_ci,
	ENGINE=InnoDB;

CREATE TABLE `sonerezh`.`songs` (
	`id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`album` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`artist` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`source_path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
	`cover` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
	`playtime` varchar(9) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
	`track_number` int(5) UNSIGNED DEFAULT NULL,
	`year` int(4) UNSIGNED DEFAULT NULL,
	`disc` varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
	`band` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
	`genre` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
	`created` datetime NOT NULL,
	`modified` datetime NOT NULL,	PRIMARY KEY  (`id`)) 	DEFAULT CHARSET=utf8,
	COLLATE=utf8_general_ci,
	ENGINE=InnoDB;

CREATE TABLE `sonerezh`.`users` (
	`id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	`email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`role` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,	PRIMARY KEY  (`id`)) 	DEFAULT CHARSET=utf8,
	COLLATE=utf8_general_ci,
	ENGINE=InnoDB;

