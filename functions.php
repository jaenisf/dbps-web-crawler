<?php	
	// get links from the crawler
	function crawler_get_links($crawler)
	{
		return $links = $crawler->get('links');
	}
	
	// get title from the crawler
	function crawler_get_title($crawler)
	{
		$titles = $crawler->get('titles');
		return !empty($titles) ? $titles[0] : "";
	}
	
	// get words from the crawler
	function crawler_get_words($crawler)
	{
		return $words = $crawler->get('words');
	}
	
	// check if link is in database
	function check_if_link_is_in_database($mysqli, $link)
	{
		$sql = "SELECT * FROM `link` WHERE url = '$link';";
		$result = $mysqli->query($sql);

		if ($result->num_rows > 0) 
		{
			return true;		
		} 
		else 
		{
			return false;
		}
	}
	
	// check if link is up to date in_database
	function check_if_link_is_up_to_date_in_database($mysqli, $link)
	{
		$sql = "SELECT * FROM `link` WHERE url = '$link' AND time_stamp > TIMESTAMP(DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 HOUR));";
		$result = $mysqli->query($sql);

		if ($result->num_rows > 0) 
		{
			return true;		
		} 
		else 
		{
			return false;
		}
	}
	
	// add link to database
	function add_link_to_database($mysqli, $link, $title)
	{
		$sql = "INSERT IGNORE INTO `link` (url, time_stamp, title) VALUES ('$link', (SELECT CURRENT_TIMESTAMP), '$title');";
		query($mysqli, $sql);
	}
	
	// add link without crawling to database
	function add_link_without_crawling_to_database($mysqli, $link, $title)
	{
		$sql = "INSERT IGNORE INTO `link` (url, time_stamp, title) VALUES ('$link', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 HOUR), '$title');";
		query($mysqli, $sql);
	}
	
	// update link in database
	function update_link_in_database($mysqli, $link, $title)
	{
		$sql = "UPDATE `link` SET time_stamp = CURRENT_TIMESTAMP, title = '$title' WHERE url = '$link';";
		query($mysqli, $sql);
	}
	
	// add relation link refers to link to database
	function add_relation_link_refers_to_link_to_database($mysqli, $link_from, $link_to)
	{
		$sql = "SELECT (SELECT id FROM link WHERE url = '$link_from') AS id_link_from,
					   (SELECT id FROM link WHERE url = '$link_to') AS id_link_to;";
		$result = $mysqli->query($sql);
		
		if ($result->num_rows == 1) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id_link_from = $row["id_link_from"];
				$id_link_to = $row["id_link_to"];
			}
			
			$sql = "SELECT * FROM `link_refers_to_link` WHERE id_link_from = '$id_link_from' AND id_link_to = '$id_link_to';";
			$result = $mysqli->query($sql);
			
			if ($result->num_rows == 0) 
			{
				$sql = "INSERT IGNORE INTO `link_refers_to_link` (id_link_from, id_link_to) VALUES ($id_link_from, $id_link_to);";
				query($mysqli, $sql);
			}
		}
	}
	
	// delete relation link refers to link to database
	function delete_relation_link_refers_to_link_to_database($mysqli, $link_from, $link_to)
	{
		$sql = "SELECT (SELECT id FROM link WHERE url = '$link_from') AS id_link_from,
					   (SELECT id FROM link WHERE url = '$link_to') AS id_link_to;";
		$result = $mysqli->query($sql);
		
		if ($result->num_rows == 1) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id_link_from = $row["id_link_from"];
				$id_link_to = $row["id_link_to"];
			}
			
			$sql = "DELETE FROM `link_refers_to_link` WHERE id_link_from = $id_link_from AND id_link_to = $id_link_to;";
			query($mysqli, $sql);
		}
	}
	
	// delete relations link refers to in database
	function delete_relations_link_refers_to_in_database($mysqli, $link_from)
	{
		$sql = "SELECT (SELECT id FROM link WHERE url = '$link_from') AS id_link_from;";
		$result = $mysqli->query($sql);
		
		if ($result->num_rows == 1) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id_link_from = $row["id_link_from"];
			}
			
			$sql = "DELETE FROM `link_refers_to_link` WHERE id_link_from = $id_link_from;";
			query($mysqli, $sql);
		}
	}
	
	// check if word is in database
	function check_if_word_is_in_database($mysqli, $word)
	{
		$sql = "SELECT * FROM `word` WHERE word = '$word';";
		$result = $mysqli->query($sql);

		if ($result->num_rows > 0) 
		{
			return true;		
		} 
		else 
		{
			return false;
		}
	}
	
	// add word to database
	function add_word_to_database($mysqli, $word)
	{
		$sql = "INSERT IGNORE INTO `word` (word) VALUES ('$word');";
		query($mysqli, $sql);
	}
	
	// check if stop word is in database
	function check_if_stop_word_is_in_database($mysqli, $word)
	{
		$sql = "SELECT * FROM `stop_word` WHERE stop_word = '$word';";
		$result = $mysqli->query($sql);

		if ($result->num_rows > 0) 
		{
			return true;		
		} 
		else 
		{
			return false;
		}
	}
	
	// check if relation word is in link is in database
	function check_if_relation_word_is_in_link_is_in_database($mysqli, $link, $word)
	{
		$sql = "SELECT (SELECT id AS id_word FROM word WHERE word = '$word') AS id_word,
					   (SELECT id FROM link WHERE url = '$link') AS id_link;";
		$result = $mysqli->query($sql);
		
		if ($result->num_rows == 1) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id_word = $row["id_word"];
				$id_link = $row["id_link"];
			}
			
			$sql = "SELECT * FROM `word_is_in_link` WHERE id_word = '$id_word' AND id_link = '$id_link';";
			$result = $mysqli->query($sql);

			if ($result->num_rows > 0) 
			{
				return true;		
			} 
			else 
			{
				return false;
			}
		}
		
		return false;
	}
	
	// add relation word is in link to database
	function add_relation_word_is_in_link_to_database($mysqli, $link, $word, $count)
	{
		$sql = "SELECT (SELECT id AS id_word FROM word WHERE word = '$word') AS id_word,
					   (SELECT id FROM link WHERE url = '$link') AS id_link;";
		$result = $mysqli->query($sql);
		
		if ($result->num_rows == 1) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id_word = $row["id_word"];
				$id_link = $row["id_link"];
			}
			
			$sql = "INSERT IGNORE INTO `word_is_in_link` (id_word, id_link, count) VALUES ($id_word, $id_link, $count);";
			query($mysqli, $sql);
		}
	}
	
	// update relation relation word is in link to database
	function update_relation_word_is_in_link_to_database($mysqli, $link, $word, $count)
	{
		$sql = "SELECT (SELECT id AS id_word FROM word WHERE word = '$word') AS id_word,
					   (SELECT id FROM link WHERE url = '$link') AS id_link;";
		$result = $mysqli->query($sql);
		
		if ($result->num_rows == 1) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id_word = $row["id_word"];
				$id_link = $row["id_link"];
			}
			
			$sql = "UPDATE `word_is_in_link` SET count = $count WHERE id_word = $id_word AND id_link = $id_link;";
			query($mysqli, $sql);
		}
	}
	
	// delete relations word is in link from database
	function delete_relations_word_is_in_link_from_database($mysqli, $link)
	{
		$sql = "SELECT (SELECT id FROM link WHERE url = '$link') AS id_link;";
		$result = $mysqli->query($sql);
		
		if ($result->num_rows == 1) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id_link = $row["id_link"];
			}
			
			$sql = "DELETE FROM `word_is_in_link` WHERE id_link = $id_link;";
			query($mysqli, $sql);
		}
	}
	
	// add relation word is stop word to database
	function add_relation_word_is_stop_word_to_database($mysqli, $word)
	{
		$sql = "SELECT (SELECT id FROM word WHERE word = '$word') AS id_word, 
					   (SELECT id AS id_stop_word FROM stop_word WHERE stop_word = '$word') AS id_stop_word;";
		$result = $mysqli->query($sql);

		if ($result->num_rows == 1) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id_word = $row["id_word"];
				$id_stop_word = $row["id_stop_word"];
			}
			
			$sql = "INSERT IGNORE INTO `word_is_stop_word` (id_word, id_stop_word) VALUES ($id_word, $id_stop_word);";
			query($mysqli, $sql);
		}
	}
?>