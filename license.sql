CREATE DATABASE IF NOT EXISTS `license`;
USE `license`;

DROP TABLE IF EXISTS `jbt_jti_blacklist_table`;
CREATE TABLE IF NOT EXISTS `jbt_jti_blacklist_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `json_token_identifier` varchar(100) DEFAULT NULL,
  `jti_add_date_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=LATIN1;

DROP TABLE IF EXISTS `lal_license_api_log`;
CREATE TABLE IF NOT EXISTS `lal_license_api_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_details` longtext NOT NULL,
  `input_details` longtext NOT NULL,
  `comments` longtext NOT NULL,
  `inserted_date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=LATIN1;


-- Dumping structure for table test.ldt_license_details
DROP TABLE IF EXISTS `ldt_license_details`;
CREATE TABLE IF NOT EXISTS `ldt_license_details` (
  `ldt_id` int(11) NOT NULL AUTO_INCREMENT,
  `ldt_license` varchar(300) NOT NULL,
  `ldt_is_active` char(1) NOT NULL,
  `ldt_entered_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ldt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;


INSERT INTO `ldt_license_details` (`ldt_id`, `ldt_license`, `ldt_is_active`, `ldt_entered_date`) VALUES
	(1, '987654321', 'Y', '2021-01-06 10:32:45');

DROP TABLE IF EXISTS `lgt_license_global_table`;
CREATE TABLE IF NOT EXISTS `lgt_license_global_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `global_key` varchar(100) DEFAULT NULL,
  `global_value` varchar(25) DEFAULT NULL,
  `is_active` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;


INSERT INTO `lgt_license_global_table` (`id`, `global_key`, `global_value`, `is_active`) VALUES
	(1, 'concurrent_users', '2', 'Y'),
	(2, 'allowed_devices', '3', 'Y'),
	(3, 'token_expiration_time', '10', 'Y');

DROP TABLE IF EXISTS `license_his_user_log`;
CREATE TABLE IF NOT EXISTS `license_his_user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `pat_login_time` datetime DEFAULT NULL,
  `pat_logout_time` datetime DEFAULT NULL,
  `login_status` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logout_status` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pat_sys_ip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pat_sys_browser` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pat_sys_os` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pat_dev_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pat_guid` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login_exceeded` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `device_exceeded` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_status` (`login_status`),
  KEY `pat_login_time` (`pat_login_time`),
  KEY `pat_sys_browser` (`pat_sys_browser`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=UTF8_UNICODE_CI;


DROP TABLE IF EXISTS `lmg_license_response_messages`;
CREATE TABLE IF NOT EXISTS `lmg_license_response_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `licenseid` int(11) DEFAULT NULL,
  `respkey` varchar(50) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=LATIN1;

INSERT INTO `lmg_license_response_messages` (`id`, `licenseid`, `respkey`, `value`) VALUES
	(1, 1, 'LCE', 'You cannot login at this time, as the concurrent licensed users queue is full, please try after sometime'),
	(2, 1, 'ADE', 'You have logged in on different devices, please logout from them to continue login here'),
	(3, 1, 'LS', 'You have successfully loggedin'),
	(4, 1, 'VPWD', 'Please enter a valid password'),
	(5, 1, 'VUNAME', 'Please enter a valid email');

DROP TABLE IF EXISTS `udt_user_details`;
CREATE TABLE IF NOT EXISTS `udt_user_details` (
  `udt_id` int(11) NOT NULL AUTO_INCREMENT,
  `udt_licenseid` varchar(50) NOT NULL,
  `udt_email` varchar(50) NOT NULL,
  `udt_password` varchar(100) NOT NULL,
  `udt_firstname` varchar(100) NOT NULL,
  `udt_lastname` varchar(100) NOT NULL,
  `udt_active` char(1) NOT NULL,
  `udt_entered_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`udt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1101 DEFAULT CHARSET=latin1;


INSERT INTO `udt_user_details` (`udt_id`, `udt_licenseid`, `udt_email`, `udt_password`, `udt_firstname`, `udt_lastname`, `udt_active`, `udt_entered_date`) VALUES
	(1, '987654321', 'dineshkumar.devaraj@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Dineshkumar', 'Devaraj', 'Y', '2021-01-05 18:44:54'),
	(2, '987654321', 'srinivasan.k@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Srinivasan', 'Kittusamy', 'Y', '2021-01-05 18:46:52'),
	(3, '987654321', 'dhanakumar.m@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Dhanakumar', 'Marappan', 'Y', '2021-01-05 18:46:52'),
	(4, '987654321', 'kanagaraj.r@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Kanagaraj', 'Rajagounder', 'Y', '2021-01-05 18:46:52'),
	(5, '987654321', 'tamilselvan.p@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Tamilselvan', 'Paramasivam', 'Y', '2021-01-05 18:46:52'),
	(6, '987654321', 'gowthamraj.v@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Gowthamraj', 'Veerabatharan', 'Y', '2021-01-05 18:46:52'),
	(7, '987654321', 'balu.p@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Balu', 'Periasamy', 'Y', '2021-01-05 18:51:47'),
	(8, '987654321', 'sathishkanna.s@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Sathishkanna', 'Selvam', 'Y', '2021-01-05 18:51:47'),
	(9, '987654321', 'sathiyaraj.r@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Sathiyaraj', 'Ramar', 'Y', '2021-01-05 18:51:47'),
	(10, '987654321', 'mahendran.k@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Mahendran', 'Kalaraj', 'Y', '2021-01-05 18:51:47'),
	(11, '987654321', 'santhosh.s@kgisl.com', 'f858edae4d37e500d16baee012a414d7', 'Santhosh', 'Sampath Kumar', 'Y', '2021-01-05 18:51:47');

