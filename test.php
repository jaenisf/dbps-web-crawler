<?php
	set_time_limit(60*60);
	#error_reporting(0);

	include('connect.php');
	
	include('query.php');
	
	include('initialize.php');
	
	include('crawler.php');
	
	include('text-to-words-function.php');
	
	function check_if_link_is_in_database($mysqli, $link)
	{
		$sql = "SELECT * FROM `link` WHERE url = '$link';";
		$result = $mysqli->query($sql);

		if ($result->num_rows > 0) {
			return true;		
		} else {
			return false;
		}
	}
	
	function check_if_link_is_up_to_date_in_database($mysqli, $link)
	{
		$sql = "SELECT * FROM `link` WHERE url = '$link' AND time_stamp > TIMESTAMP(DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 HOUR));";
		$result = $mysqli->query($sql);

		if ($result->num_rows > 0) {
			return true;		
		} else {
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
	
	function check_if_word_is_in_database($mysqli, $word)
	{
		$sql = "SELECT * FROM `word` WHERE word = '$word';";
		$result = $mysqli->query($sql);

		if ($result->num_rows > 0) {
			return true;		
		} else {
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

		if ($result->num_rows > 0) {
			return true;		
		} else {
			return false;
		}
	}
	
	function add_relation_word_is_in_link_to_database($mysqli, $link, $word, $count)
	{
		$sql = "SELECT (SELECT id AS id_word FROM word WHERE word = '$word') AS id_word,
					   (SELECT id FROM link WHERE url = '$link') AS id_link;";
		$result = $mysqli->query($sql);
		
		if ($result->num_rows == 1) {
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

		if ($result->num_rows == 1) {
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
	function crawl($mysqli, $link, $maxDepth)
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
				} else {
					$boolean_crawl = false;
				}
			} else {
				add_link_to_database($mysqli, $link, $title);
				$boolean_crawl = true;
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
			
			echo "<hr>";
			
			if ($maxDepth >= 0 AND $boolean_crawl == true) {
			
				$links = crawler_get_links($crawler);
				
				/*echo "<pre>";
				print_r($links);
				echo "</pre>";*/
				
				if ($links != false AND !empty($links))
				{
					foreach ($links as $link)
					{				
						if (str_starts_with($link, 'http://') == false AND str_starts_with($link, 'https://') == false) 
						{
							if (str_starts_with($link, '/')) {
								$link = ($crawler->base).$link;
							} else {
								$link = ($crawler->base)."/".$link;
							}
						}
						
						if (filter_var($link, FILTER_VALIDATE_URL)) {
							crawl($mysqli, $link, $maxDepth - 1);
						} else {
							echo "Die URL des Links zum Crawlen konnte nicht korrekt umgewandelt werden!";
						}
						
						echo "<hr>";
						
						/*wenn ($link in der Form "/...")
						füge $link links "http://<hostname_von_$URL>" an
						wenn ($link in der Form "...")
						füge $link links
						"http://<hostname_von_$URL>/<dir_von_$URL>/" an
						wenn ($link in der DB nicht als besuchte Seite
						gespeichert ist)
						crawl ( $link );*/
					}
				} else {
					echo "Die maximale Rekursionstiefe wurde erreicht!";
				}
			}
		} else {
			echo "Die URL muss als relativer Pfad mit dem Protokoll http oder https angegeben werden!";
		}
	}
?>

<html>
	<body>
		<h2>Webcrawler</h2>
		
<?php
	#crawl($mysqli, 'http://www.dhbw-heidenheim.de', 1);
	#crawl($mysqli, 'https://de.wikipedia.org/wiki/Rainer_Kuhlen', 1);
	crawl($mysqli, 'https://www.heidenheim.de', 1);
?>

	</body>
</html>

<?php
	include('close.php');
?>