SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `db_table`;
CREATE TABLE `db_table` (
  `id` varchar(255) NOT NULL,
  `locale` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `db_table` (`id`, `locale`, `message`) VALUES
('hello',	'cs_CZ',	'Ahoj'),
('hello',	'en_US',	'Hello');
