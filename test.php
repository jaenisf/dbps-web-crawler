<?php
	set_time_limit(60*5);
	#error_reporting(0);

	include('connect.php');
	
	include('query.php');
	
	include('crawler.php');
	
	include('text-to-words-function.php');
	
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
	
	function add_link_to_database($mysqli, $link, $title)
	{
		$sql = "INSERT INTO `link` (url, time_stamp, title) VALUES ('$link', (SELECT CURRENT_TIMESTAMP), '$title');";
		query($mysqli, $sql);
	}
	
	function update_link_in_database($mysqli, $link, $title)
	{
		$sql = "UPDATE `link` SET time_stamp = CURRENT_TIMESTAMP, title = '$title' WHERE url = '$link';";
		query($mysqli, $sql);
	}
	
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
				$sql = "INSERT INTO `link_refers_to_link` (id_link_from, id_link_to) VALUES ($id_link_from, $id_link_to);";
				query($mysqli, $sql);
			}
		}
	}
	
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
	
	function add_word_to_database($mysqli, $word)
	{
		$sql = "INSERT INTO `word` (word) VALUES ('$word');";
		query($mysqli, $sql);
	}
	
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
			
			$sql = "INSERT INTO `word_is_in_link` (id_word, id_link, count) VALUES ($id_word, $id_link, $count);";
			query($mysqli, $sql);
		}
	}
	
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
			
			$sql = "INSERT INTO `word_is_stop_word` (id_word, id_stop_word) VALUES ($id_word, $id_stop_word);";
			query($mysqli, $sql);
		}
	}
	
	function crawler_get_links($crawler)
	{
		return $links = $crawler->get('links');
	}
	
	function crawler_get_title($crawler)
	{
		$titles = $crawler->get('titles');
		return !empty($titles) ? $titles[0] : "";
	}
	
	function crawler_get_words($crawler)
	{
		return $words = $crawler->get('words');
	}
?>

<?php
	function crawl($mysqli, $link, $link_from, $maxDepth)
	{
		if(filter_var($link, FILTER_VALIDATE_URL) AND (str_starts_with($link, 'http://') OR str_starts_with($link, 'https://')))
		{
			$crawler = new Crawler($link);
			
			echo $link."<br>";
			
			$title = crawler_get_title($crawler);
			$title = str_replace("\"", "", $title);
			$title = str_replace("'", "", $title);
			
			if (check_if_link_is_in_database($mysqli, $link) == true)
			{
				if (check_if_link_is_up_to_date_in_database($mysqli, $link) == false)
				{
					update_link_in_database($mysqli, $link, $title);
					$boolean_crawl = true;
				} 
				else 
				{
					$boolean_crawl = false;
				}
			} 
			else 
			{
				add_link_to_database($mysqli, $link, $title);
				$boolean_crawl = true;
			}
			
			if ($link_from != "")
			{
				add_relation_link_refers_to_link_to_database($mysqli, $link_from, $link);
			}
			
			$words = crawler_get_words($crawler);
			
			if ($words != false AND !empty($words) AND $boolean_crawl == true)
			{
				foreach ($words as $word => $count)
				{
					if (check_if_word_is_in_database($mysqli, $word) == false AND strlen($word) <= 255)
					{
						add_word_to_database($mysqli, $word);
						
						if (check_if_stop_word_is_in_database($mysqli, $word) == true)
						{
							add_relation_word_is_stop_word_to_database($mysqli, $word);
						}
					}
					
					if (strlen($word) <= 255)
					{
						add_relation_word_is_in_link_to_database($mysqli, $link, $word, $count);
					}
				}
			}
			
			$links = crawler_get_links($crawler);
			
			if ($links != false AND !empty($links))
			{
				foreach ($links as $link)
				{
					if (check_if_link_is_in_database($mysqli, $link) == true)
					{
						add_relation_link_refers_to_link_to_database($mysqli, ($crawler->base), $link);
					}
				}
			}
			
			echo "<hr>";
			
			if ($maxDepth >= 0 AND $boolean_crawl == true AND $links != false AND !empty($links))
			{				
				foreach ($links as $link)
				{				
					if (str_starts_with($link, 'http://') == false AND str_starts_with($link, 'https://') == false) 
					{
						if (str_starts_with($link, '/')) 
						{
							$link = ($crawler->base).$link;
						} 
						else 
						{
							$link = ($crawler->base)."/".$link;
						}
					}
					
					if (filter_var($link, FILTER_VALIDATE_URL)) 
					{
						crawl($mysqli, $link, ($crawler->base), $maxDepth - 1);
					} 
					else 
					{
						echo "Die URL des Links zum Crawlen konnte nicht korrekt umgewandelt werden!";
					}
					
					echo "<hr>";
				}
			} 
			else 
			{
				echo "Die maximale Rekursionstiefe wurde erreicht!";
			}
		} 
		else 
		{
			echo "Die URL muss als relativer Pfad mit dem Protokoll http oder https angegeben werden!";
		}
	}
?>

<html>
	<body>
		<h2>Webcrawler</h2>
		
<?php
	#crawl($mysqli, 'http://www.dhbw-heidenheim.de', "", 1);
	#crawl($mysqli, 'https://de.wikipedia.org/wiki/Rainer_Kuhlen', "", 1);
	#crawl($mysqli, 'https://www.heidenheim.de', "", 1);
	
    if (isset($_GET['mode'])) {
        $mode = $_GET['mode'];
    } else {
        $mode = "search";
        echo "The 'mode' argument is missing. Possible are 'search', 'add_link' and 'worker'. Using default 'search' ...";
    } 
	
	if ($mode == 'test') {
		include('initialize.php');
	}
	
	$sql = "USE `dbps-web-crawler`;";
	query($mysqli, $sql);
	
	if ($mode == 'search')
    {
?>
	<form action="" method="POST" id="search_form" style="text-align: center; padding: 20px; border: 1px solid black;">
		<input type="text" name="search" id="search" minlength="1" maxlength="50">
		&emsp;
		<input type="submit" value="Suchen" name="submit_search">
	</form>
<?php
		if (isset($_POST['submit_search']))
		{
			if(trim($_POST['search']) != "")
			{
				$search_words = htmlspecialchars($_POST['search']); // Freie Stellen Feuerwehr Heidenheim
				$search_words = text_to_words_array_with_count($search_words);
				
				/*echo "<pre>";
				print_r($search_words);
				echo "</pre>";*/
				
				$sql = "SELECT *, 
							   COUNT(count) AS anzahl_verschiedene_woerter, 
							   SUM(count) AS anzahl_vorkommen_suchwoerter
						FROM word
						LEFT JOIN word_is_in_link ON word.id = word_is_in_link.id_word
						LEFT JOIN link ON word_is_in_link.id_link = link.id
						WHERE ";
				
				foreach ($search_words as $word => $count)
				{
					$sql = $sql."word LIKE '%$word%' OR ";
				}
				
				$sql = substr($sql, 0, strlen($sql) - 3);
				
				$sql = $sql."GROUP BY id_link 
							 ORDER BY anzahl_verschiedene_woerter DESC, 
								  anzahl_vorkommen_suchwoerter DESC
							 LIMIT 20;";
				
				$result = $mysqli->query($sql);
							   
				if (empty($result)) echo $mysqli->error;

				if ($result->num_rows > 0) {
					echo "==> Top 20 Suchergebnisse<br><ul>";
					
					while($row = $result->fetch_assoc()) 
					{
						$url = $row["url"];
						$title = $row["title"];
						
						if ($row["title"] != "")
						{
							$title = "(".$title.")";
						}
						
						echo $url = "<li style='padding: 5px;'>
										<a href='".$url."' style='color: #00B2C3;'>".$url."</a>
										".$title."
									</li>";
					}
					
					echo "</ul>";
				} else {
					echo "Es konnten keine Suchergebnisse gefunden werden!";
				}
			}
		}
    }
	else if ($mode == 'add_link')
    {
        
    }
    else if ($mode == "worker")
    {
        
    }
	else if ($mode == "test")
    {
        crawl($mysqli, 'https://www.heidenheim.de', "", 1);
    }
    else
    {
        echo "Error: Crawler mode [".$crawler_mode."] not supported.<br>";
    }
?>

	</body>
</html>

<?php
	include('close.php');
?>