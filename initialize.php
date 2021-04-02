<?php
	$sql = "DROP DATABASE IF EXISTS `dbps-web-crawler`;";
	query($mysqli, $sql);
	
	$sql = "CREATE DATABASE IF NOT EXISTS `dbps-web-crawler` /*!40100 DEFAULT CHARACTER SET utf8 */;";
	query($mysqli, $sql);
	
	$sql = "USE `dbps-web-crawler`;";
	query($mysqli, $sql);
	
	$sql = "DROP TABLE IF EXISTS `link`;";
	query($mysqli, $sql);
	
	$sql = "CREATE TABLE IF NOT EXISTS `link` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `url` mediumtext DEFAULT NULL,
			  `time_stamp` timestamp NULL DEFAULT NULL,
			  `title` char(255) DEFAULT NULL,
			  `page_rank` float DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	query($mysqli, $sql);
	
	$sql = "DROP TABLE IF EXISTS `link_refers_to_link`;";
	query($mysqli, $sql);
	
	$sql = "CREATE TABLE IF NOT EXISTS `link_refers_to_link` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `id_link_from` int(11) DEFAULT NULL,
			  `id_link_to` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `id_link_from_id_link_to` (`id_link_from`,`id_link_to`),
			  KEY `FK_link_refers_to_link_link_2` (`id_link_to`),
			  CONSTRAINT `FK_link_refers_to_link_link` FOREIGN KEY (`id_link_from`) REFERENCES `link` (`id`),
			  CONSTRAINT `FK_link_refers_to_link_link_2` FOREIGN KEY (`id_link_to`) REFERENCES `link` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	query($mysqli, $sql);
	
	$sql = "DROP TABLE IF EXISTS `stop_word`;";
	query($mysqli, $sql);
	
	$sql = "CREATE TABLE IF NOT EXISTS `stop_word` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `stop_word` char(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `stop_word` (`stop_word`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	query($mysqli, $sql);
	
	$sql = "DROP TABLE IF EXISTS `word`;";
	query($mysqli, $sql);
	
	$sql = "CREATE TABLE IF NOT EXISTS `word` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `word` char(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `word` (`word`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	query($mysqli, $sql);
	
	$sql = "DROP TABLE IF EXISTS `word_is_in_link`;";
	query($mysqli, $sql);
	
	$sql = "CREATE TABLE IF NOT EXISTS `word_is_in_link` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `id_word` int(11) NOT NULL,
			  `id_link` int(11) NOT NULL,
			  `count` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `id_word_id_link` (`id_word`,`id_link`),
			  KEY `FK_word_is_in_link_link` (`id_link`),
			  CONSTRAINT `FK_word_is_in_link_link` FOREIGN KEY (`id_link`) REFERENCES `link` (`id`),
			  CONSTRAINT `FK_word_is_in_link_word` FOREIGN KEY (`id_word`) REFERENCES `word` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	query($mysqli, $sql);
	
	$sql = "DROP TABLE IF EXISTS `word_is_stop_word`;";
	query($mysqli, $sql);
	
	$sql = "CREATE TABLE IF NOT EXISTS `word_is_stop_word` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `id_word` int(11) DEFAULT NULL,
			  `id_stop_word` int(11) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `id_word_id_stop_word` (`id_word`,`id_stop_word`),
			  KEY `FK_word_is_stop_word_word_2` (`id_stop_word`),
			  CONSTRAINT `FK_word_is_stop_word_word` FOREIGN KEY (`id_word`) REFERENCES `word` (`id`),
			  CONSTRAINT `FK_word_is_stop_word_stop_word` FOREIGN KEY (`id_stop_word`) REFERENCES `stop_word` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	query($mysqli, $sql);
	
	foreach (json_decode(file_get_contents("stopwords-de.json"), true) as $element)
	{
		$element = utf8_encode($element);
		$element = str_replace("'", "''", $element);
		
		$sql = "INSERT INTO `stop_word` (stop_word) SELECT '".$element."'
				WHERE NOT EXISTS (SELECT * FROM stop_word WHERE stop_word = '".$element."');";
		query($mysqli, $sql);
	}
	
	foreach (json_decode(file_get_contents("stopwords-en.json"), true) as $element)
	{
		$element = utf8_encode($element);
		$element = str_replace("'", "''", $element);
		
		$sql = "INSERT INTO `stop_word` (stop_word) SELECT '".$element."'
				WHERE NOT EXISTS (SELECT * FROM stop_word WHERE stop_word = '".$element."');";
		query($mysqli, $sql);
	}
?>