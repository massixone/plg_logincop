/*
Drop the #__user_login_cop`. This will implicitly drop all indexes.
*/
DROP TABLE IF EXISTS `#__user_login_cop`;

/*
Create the tracking storage table
*/
CREATE TABLE `vykg9_user_login_cop` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `action` smallint(6) NOT NULL DEFAULT '0' COMMENT 'action: 0 = Unsuccessful login, 1 = Successfull login',
  `userid` int(11) NOT NULL COMMENT 'the Joomla! User ID',
  `username` varchar(40) NOT NULL COMMENT 'the Joomla! Username',
  `ip` varchar(20) NOT NULL COMMENT 'The IP address of the client attempting the login',
  `time_login` datetime NOT NULL COMMENT 'the time when the login attempt happened',
  `time_logout` datetime DEFAULT NULL COMMENT 'the time when the logout happened (if any)',
  PRIMARY KEY (`id`),
  KEY `key_time_login` (`time_login`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8;

