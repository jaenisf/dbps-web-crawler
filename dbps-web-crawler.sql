-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               10.4.17-MariaDB - mariadb.org binary distribution
-- Server Betriebssystem:        Win64
-- HeidiSQL Version:             11.0.0.5919
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportiere Struktur von Tabelle dbps-web-crawler.link
CREATE TABLE IF NOT EXISTS `link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` mediumtext DEFAULT NULL,
  `time_stamp` timestamp NULL DEFAULT NULL,
  `title` char(255) DEFAULT NULL,
  `page_rank` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Daten Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle dbps-web-crawler.link_refers_to_link
CREATE TABLE IF NOT EXISTS `link_refers_to_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_link_from` int(11) DEFAULT NULL,
  `id_link_to` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_link_from_id_link_to` (`id_link_from`,`id_link_to`),
  KEY `FK_link_refers_to_link_link_2` (`id_link_to`),
  CONSTRAINT `FK_link_refers_to_link_link` FOREIGN KEY (`id_link_from`) REFERENCES `link` (`id`),
  CONSTRAINT `FK_link_refers_to_link_link_2` FOREIGN KEY (`id_link_to`) REFERENCES `link` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Daten Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle dbps-web-crawler.stop_word
CREATE TABLE IF NOT EXISTS `stop_word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stop_word` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stop_word` (`stop_word`)
) ENGINE=InnoDB AUTO_INCREMENT=1875 DEFAULT CHARSET=utf8mb4;

-- Daten Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle dbps-web-crawler.word
CREATE TABLE IF NOT EXISTS `word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `word` (`word`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

-- Daten Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle dbps-web-crawler.word_is_in_link
CREATE TABLE IF NOT EXISTS `word_is_in_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_word` int(11) NOT NULL,
  `id_link` int(11) NOT NULL,
  `count` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_word_id_link` (`id_word`,`id_link`),
  KEY `FK_word_is_in_link_link` (`id_link`),
  CONSTRAINT `FK_word_is_in_link_link` FOREIGN KEY (`id_link`) REFERENCES `link` (`id`),
  CONSTRAINT `FK_word_is_in_link_word` FOREIGN KEY (`id_word`) REFERENCES `word` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4;

-- Daten Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle dbps-web-crawler.word_is_stop_word
CREATE TABLE IF NOT EXISTS `word_is_stop_word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_word` int(11) DEFAULT NULL,
  `id_stop_word` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_word_id_stop_word` (`id_word`,`id_stop_word`),
  KEY `FK_word_is_stop_word_word_2` (`id_stop_word`),
  CONSTRAINT `FK_word_is_stop_word_stop_word` FOREIGN KEY (`id_stop_word`) REFERENCES `stop_word` (`id`),
  CONSTRAINT `FK_word_is_stop_word_word` FOREIGN KEY (`id_word`) REFERENCES `word` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

-- Daten Export vom Benutzer nicht ausgewählt

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
