CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `test` tinyint(4) NOT NULL default '0',
  `username` varchar(50) NOT NULL,
  `openid` varchar(100) NOT NULL,
  `accepted_eula` tinyint(4) NOT NULL default '0',
  `registration_date` date NOT NULL,
  `password` char(40) NOT NULL,
  `password_changed` date NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `token` char(32) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;

INSERT INTO `users` (`id`, `test`, `username`, `accepted_eula`, `registration_date`, `password`, `password_changed`, `firstname`, `lastname`, `email`, `role`, `token`) VALUES
(1, 0, 'admin', 1, '', '21232f297a57a5a743894a0e4a801fc3', '2008-10-28', 'Admin', 'User', 'whateva@mailinator.com', 'admin', '');


CREATE TABLE `associations` (
  `server_url` blob NOT NULL,
  `handle` varchar(255) NOT NULL,
  `secret` blob NOT NULL,
  `issued` int(11) NOT NULL,
  `lifetime` int(11) NOT NULL,
  `assoc_type` varchar(64) NOT NULL,
  PRIMARY KEY  (`server_url`(255),`handle`)
) ENGINE=MyISAM;

CREATE TABLE `nonces` (
  `server_url` varchar(2047) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `salt` char(40) NOT NULL,
  UNIQUE KEY `server_url` (`server_url`(255),`timestamp`,`salt`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `sites` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `site` varchar(100) NOT NULL,
  `creation_date` date NOT NULL,
  `trusted` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

ALTER TABLE `sites`
  ADD CONSTRAINT `sites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


CREATE TABLE `history` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `site` varchar(100) NOT NULL default '',
  `ip` varchar(32) NOT NULL default '',
  `result` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

ALTER TABLE `history`
  ADD CONSTRAINT `history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


CREATE TABLE `fields` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `openid` varchar(12) NOT NULL,
  `type` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `fields_values` (
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`user_id`,`field_id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB;

ALTER TABLE `fields_values`
  ADD CONSTRAINT `fields_values_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fields_values_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE;

INSERT INTO `fields` (`id`, `name`, `openid`, `type`) VALUES(1, 'Nickname', 'nickname', 1);
INSERT INTO `fields` (`id`, `name`, `openid`, `type`) VALUES(2, 'E-mail', 'email', 7);
INSERT INTO `fields` (`id`, `name`, `openid`, `type`) VALUES(3, 'Full Name', 'fullname', 1);
INSERT INTO `fields` (`id`, `name`, `openid`, `type`) VALUES(4, 'Date of Birth', 'dob', 2);
INSERT INTO `fields` (`id`, `name`, `openid`, `type`) VALUES(5, 'Gender', 'gender', 3);
INSERT INTO `fields` (`id`, `name`, `openid`, `type`) VALUES(6, 'Postal Code', 'postcode', 1);
INSERT INTO `fields` (`id`, `name`, `openid`, `type`) VALUES(7, 'Country', 'country', 4);
INSERT INTO `fields` (`id`, `name`, `openid`, `type`) VALUES(8, 'Language', 'language', 5);
INSERT INTO `fields` (`id`, `name`, `openid`, `type`) VALUES(9, 'Time Zone', 'timezone', 6);

CREATE TABLE `settings` (
   `name` VARCHAR( 255 ) NOT NULL ,
   `value` VARCHAR( 255 ) NOT NULL ,
   PRIMARY KEY ( `name` )
 ) ENGINE = MYISAM ;

INSERT INTO `settings` (`name`, `value`) VALUES ('maintenance_mode', '0');
INSERT INTO `settings` (`name`, `value`) VALUES ('version', '1.1.0.beta1');

CREATE TABLE `news` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `test` tinyint(4) NOT NULL,
    `title` VARCHAR( 255 ) NOT NULL ,
    `date` DATETIME NOT NULL ,
    `excerpt` TEXT NOT NULL ,
    `content` TEXT NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `auth_attempts` (
  `id` int(11) NOT NULL auto_increment,
  `IP` varchar(15) NOT NULL,
  `session_id` varchar(32) NOT NULL,
  `failed_attempts` tinyint(4) NOT NULL,
  `last_attempt` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;
