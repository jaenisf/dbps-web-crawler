<?php
	set_time_limit(60*5);
	error_reporting(0);

	include('connect.php');
	
	include('query.php');
	
	include('initialize.php');
	
	include('crawler.php');
	
	include('text-to-words-function.php');
	
	function check_if_link_is_in_database($mysqli, $link)
	{
		$sql = "SELECT * FROM links WHERE url = '$link';";
		$result = $mysqli->query($sql);

		if ($result->num_rows > 0) {
			return true;		
		} else {
			return false;
		}
	}
	
	function add_link_to_database($mysqli, $link)
	{
		$sql = 'INSERT INTO `links` (url, time_stamp) VALUES ("'.$link.'", (SELECT CURRENT_TIMESTAMP));';
		query($mysqli, $sql);
	}
	
	function update_link_in_database($mysqli, $link)
	{
		$sql = 'INSERT INTO `links` (url, time_stamp) VALUES ("'.$link.'", (SELECT CURRENT_TIMESTAMP));';
		query($mysqli, $sql);
	}
	
	function crawler_get_links($crawler)
	{
		return $links = $crawler->get('links');
	}
	
	function crawler_get_title($crawler)
	{
		$titles = $crawler->get('titels');
		return !empty($titles) ? $images[0] : FALSE;
	}
	
	function crawler_get_words($crawler)
	{
		return $words = $crawler->get('words');
	}
?>

<?php
	function crawl($mysqli, $link)
	{
		if(filter_var($link, FILTER_VALIDATE_URL) AND (str_starts_with($link, 'http://') OR str_starts_with($link, 'https://'))) {
			$crawler = new Crawler($link);
			
			echo $link."<br>";
			
			add_link_to_database($mysqli, $link);
			
			echo "<hr>";
			
			$links = crawler_get_links($crawler);
			
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
						crawl($mysqli, $link);
					} else {
						// nicht möglich
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
	crawl($mysqli, 'http://www.dhbw-heidenheim.de');
	#crawl($mysqli, 'https://de.wikipedia.org/wiki/Rainer_Kuhlen');
?>

	</body>
</html>

<?php
	include('close.php');
?>