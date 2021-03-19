<?php
	set_time_limit(60*5);

	include('connect.php');
	
	include('query.php');
	
	include('initialize.php');
	
	include('crawler.php');
	
	include('text-to-words-function.php');
?>

<?php
	function crawl($mysqli, $url)
	{
		if(str_starts_with($url, 'http://') OR str_starts_with($url, 'https://')) {
			$crawl = new Crawler($url);
			$links = $crawl->get('links');
			$titles = $crawl->get('titles');
			$words = $crawl->get('words');
			
			echo $url."<br>";
			
			// speichere $URL in die DB als besuchte Seite mit Angaben wie title-Inhalt, und Schlagworte.
			if (filter_var($url, FILTER_VALIDATE_URL)) {
				#echo("valid | $l<br>");
				
				$sql = 'INSERT INTO `links` (url, time_stamp) VALUES ("'.$url.'", (SELECT CURRENT_TIMESTAMP));';
				query($mysqli, $sql);
			} else {
				#echo("not valid | $l<br>");
			}
			
			/*echo "<pre>";
			print_r($words);
			echo "</pre>";*/
			
			/*foreach ($words as $word)
			{
				echo $word."<br>";
			}*/
			
			/*echo "<hr>";
			
			echo "$titles[0]<br>";
			
			echo "<hr>";*/
			
			foreach ($links as $link) // in für alle Links $link in der mit URL adressierten Seite
			{
				/*echo "$link<br>";
				
				if (str_starts_with($link, 'http://'))
				{
					echo "1 - ";
				} else {
					echo "0 - ";
				}
				
				if (str_starts_with($link, 'https://'))
				{
					echo "1 - ";
				} else {
					echo "0 - ";
				}
				
				if (str_starts_with($link, 'http://') == false AND str_starts_with($link, 'https://') == false)
				{
					echo "1 - ";
				} else {
					echo "0 - ";
				}*/
				
				if (str_starts_with($link, 'http://') == false AND str_starts_with($link, 'https://') == false) 
				{
					if (str_starts_with($link, '/'))
					{
						#echo $l = "$crawl->base$link<br>";
						$l = "$crawl->base$link";
					} else {
						#echo $l = "$crawl->base/$link<br>";
						$l = "$crawl->base/$link";
					}
				} else {
					#echo $link."<br>";
					$l = $link;
				}
				
				crawl($mysqli, $l);
				
				#echo "<hr>";
				
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
			echo "Die URL muss als relativer Pfad mit dem Protokoll http oder https angegeben werden!";
		}
	}
?>

<html>
	<body>
		<h2>Webcrawler</h2>
		
<?php
	$crawl = new Crawler('http://www.dhbw-heidenheim.de');
	$images = $crawl->get('images');
	$links = $crawl->get('links');
	$titles = $crawl->get('titles');

	#crawl($mysqli, 'http://www.dhbw-heidenheim.de');
	crawl($mysqli, 'https://de.wikipedia.org/wiki/Rainer_Kuhlen');
?>

	</body>
</html>

<?php
	include('close.php');
?>