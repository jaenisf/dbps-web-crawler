<?php
	// set time limit to one hour
	set_time_limit(60*60);
	
	// set error reporting to 0
	//error_reporting(0);

	// connect to the database
	include('connect.php');
	
	// include function for sql query
	include('query.php');
	
	// read get variable mode
	if (isset($_GET['mode'])) 
	{
        $mode = $_GET['mode'];
    } 
	else 
	{
        $mode = "search";
        echo "The 'mode' argument is missing. Possible are 'search', 'add_link' and 'worker'. Using default 'search' ...";
    } 
	
	// if mode is test then initialize the database
	if ($mode == 'test') {
		include('initialize.php');
	}
	
	// select the database
	$sql = "USE `dbps-web-crawler`;";
	query($mysqli, $sql);
	
	echo "<hr><br>";
	
	// include class web crawler
	include('crawler.php');
	
	// include the function the extract the words from a text
	include('text-to-words-function.php');
	
	// include more functions
	include('functions.php');
?>

<?php
	// function to crawl a link
	function crawl($mysqli, $link, $link_from, $maxDepth)
	{		
		if(filter_var($link, FILTER_VALIDATE_URL) AND (str_starts_with($link, 'http://') OR str_starts_with($link, 'https://')))
		{
			// crawl link
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
				delete_relation_link_refers_to_link_to_database($mysqli, $link_from, $link);
				add_relation_link_refers_to_link_to_database($mysqli, $link_from, $link);
			}
			
			// write word to link relation to the database
			
			$words = crawler_get_words($crawler);
			
			if ($words != false AND !empty($words) AND $boolean_crawl == true)
			{
				delete_relations_word_is_in_link_from_database($mysqli, $link);
				
				foreach ($words as $word => $count)
				{
					if (strlen($word) <= 255)
					{
						if (check_if_word_is_in_database($mysqli, $word) == false)
						{
							add_word_to_database($mysqli, $word);
							
							if (check_if_stop_word_is_in_database($mysqli, $word) == true)
							{
								add_relation_word_is_stop_word_to_database($mysqli, $word);
							}
						}
					
						if (check_if_relation_word_is_in_link_is_in_database($mysqli, $link, $word) == false)
						{
							add_relation_word_is_in_link_to_database($mysqli, $link, $word, $count);
						}
						else
						{
							update_relation_word_is_in_link_to_database($mysqli, $link, $word, $count);
						}
					}
				}
			}
			
			// write link to link relation to the database
			
			$links = crawler_get_links($crawler);
			
			if ($links != false AND !empty($links) AND $boolean_crawl == true)
			{
				delete_relations_link_refers_to_in_database($mysqli, $link);
				
				foreach ($links as $link)
				{
					if (check_if_link_is_in_database($mysqli, $link) == true)
					{
						add_relation_link_refers_to_link_to_database($mysqli, ($crawler->base), $link);
					}
					else
					{
						if(filter_var($link, FILTER_VALIDATE_URL) AND (str_starts_with($link, 'http://') OR str_starts_with($link, 'https://')))
						{
							$crawler_link = new Crawler($link);
		
							$title = crawler_get_title($crawler_link);
							$title = str_replace("\"", "", $title);
							$title = str_replace("'", "", $title);
							
							add_link_without_crawling_to_database($mysqli, $link, $title);
						}
						
						if (check_if_link_is_in_database($mysqli, $link) == true)
						{
							add_relation_link_refers_to_link_to_database($mysqli, ($crawler->base), $link);
						}
					}

					usleep(150 * 1000);
				}
			}
			
			echo "<hr>";
			
			// crawl links if the recursion depth is not reached
			
			if ($maxDepth > 0 AND $boolean_crawl == true AND $links != false AND !empty($links))
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
		<h1 style="text-decoration: underline; text-align: center;">Suchmaschine auf Basis von Webcrawler-Daten</h1>
		
<?php	
	if ($mode == 'search')
    {
?>
	<style>
		#search_form {
			text-align: center;
			padding: 20px;
			border: 2px solid black;
			border-radius: 10px;
			padding-left: 5%;
			padding-right: 20%;
			margin: 15px 5%;
		}
		
		#search {
			width: 60%;
			padding: 5px 10px;
			border: 1px solid gray;
			border-radius: 3px;
		}
		
		#search_submit {
			width: 15%;
			padding: 5px 10px;
			border: 1px solid gray;
			border-radius: 3px;
		}
	</style>
	<form action="" method="POST" id="search_form" style="">
		<input type="text" name="search" id="search" minlength="1" maxlength="50">
		&emsp;
		<input type="submit" value="Suchen" name="submit_search" id="search_submit">
	</form>
<?php
		// start search request
		if (isset($_POST['submit_search']))
		{
			echo "<br><hr><br>";
			
			if(trim($_POST['search']) != "")
			{
				// extract words from text and remove stop words
				$search_words = htmlspecialchars($_POST['search']);
				$search_words = text_to_words_array_with_count($search_words);
				
				$info =  "<b style='font-size: 15pt;'>Suchwörter (ohne Stoppwörter):&ensp;";
				
				foreach ($search_words as $word => $count)
				{
					$sql = "SELECT * FROM stop_word WHERE stop_word = '$word';";
					$result = $mysqli->query($sql);

					if ($result->num_rows > 0) 
					{
						unset($search_words[$word]);
					}
					else
					{
						$info = $info.'"'.$word.'",&ensp;';
					}
				}
				
				$info = substr($info, 0, strlen($info) - 7);
				
				if (empty($search_words) == false)
				{
					echo $info."</b><br><br>";
					
					// create and execute database query
					
					$sql = "SELECT *, 
								   COUNT(count) AS anzahl_verschiedene_woerter, 
								   SUM(count) AS anzahl_vorkommen_suchwoerter
							FROM word
							LEFT JOIN word_is_in_link ON word.id = word_is_in_link.id_word
							LEFT JOIN link ON word_is_in_link.id_link = link.id
							WHERE ";
							
					$sql_count = "SELECT COUNT(*) OVER () AS count FROM word
								  LEFT JOIN word_is_in_link ON word.id = word_is_in_link.id_word
								  LEFT JOIN link ON word_is_in_link.id_link = link.id
								  WHERE ";
					
					foreach ($search_words as $word => $count)
					{
						$sql = $sql."word LIKE '%$word%' OR ";
						$sql_count = $sql_count."word LIKE '%$word%' OR ";
					}
					
					$sql = substr($sql, 0, strlen($sql) - 3);
					$sql_count = substr($sql_count, 0, strlen($sql_count) - 3);
					
					$sql = $sql."GROUP BY id_link 
								 ORDER BY anzahl_verschiedene_woerter DESC, 
									  anzahl_vorkommen_suchwoerter DESC
								 LIMIT 20;";
								 
					$sql_count = $sql_count."GROUP BY id_link LIMIT 1;";
							
					$result = $mysqli->query($sql_count);
			
					if ($result->num_rows > 0) 
					{
						while($row = $result->fetch_assoc()) 
						{
							$count = $row["count"];
						}
					}
					
					$start = microtime(true); 
					
					$result = $mysqli->query($sql);
					
					$end = microtime(true);
					
					$time = $end - $start;
					
					echo "Ungefähr ".$count." Ergebnisse (".$time." Sekunden) <br><br>";
								   
					if (empty($result)) echo $mysqli->error;

					if ($result->num_rows > 0) 
					{
						if ($result->num_rows == 20) 
						{
							echo "<b style='font-size: 15pt;'>&rArr; Top 20 Suchergebnisse</b><br><ul>";
						}
						else 
						{
							echo "<b style='font-size: 15pt;'>&rArr; ".($result->num_rows)." Suchergebnisse</b><br><ul>";
						}
						
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
											&ensp;".$title."
										</li>";
						}
						
						echo "</ul>";
					}
				}
				else 
				{
					echo "&rArr; Dies ist keine sinnvolle Suchanfrage!<br>";
				}
			} 
			else 
			{
				echo "&rArr; Dies ist keine sinnvolle Suchanfrage!<br>";
			}
		}
    }
	else if ($mode == 'add_link')
    {
?>
	<style>
		#form {
			text-align: center;
			padding: 20px;
			border: 2px solid black;
			border-radius: 10px;
			padding-left: 5%;
			padding-right: 20%;
			margin: 15px 5%;
		}
		
		#link {
			width: 60%;
			padding: 5px 10px;
			border: 1px solid gray;
			border-radius: 3px;
		}
		
		#submit {
			width: 15%;
			padding: 5px 10px;
			border: 1px solid gray;
			border-radius: 3px;
		}
	</style>
	<form action="" method="POST" id="form" style="">
		<input type="text" name="link" id="link" minlength="1">
		&emsp;
		<input type="submit" value="Hinzufügen" name="submit_add_link" id="submit">
	</form>
<?php
		// add / crawl link to database if button was pressed
		if (isset($_POST['submit_add_link']))
		{
			$link = $_POST['link'];
			
			if(filter_var($link, FILTER_VALIDATE_URL) AND (str_starts_with($link, 'http://') OR str_starts_with($link, 'https://')))
			{
				crawl($mysqli, $link, "", 0);
			}
			else 
			{
				echo "&rArr; Dies ist kein valider Link! Die URL muss als relativer Pfad mit dem Protokoll http oder https angegeben werden!";
			}
		}
    }
    else if ($mode == "worker")
    {
		// the worker functionality
		while (true)
		{
			$sql = "SELECT * FROM link ORDER BY id ASC;";
			$result = $mysqli->query($sql);
			
			if ($result->num_rows > 0) 
			{
				while($row = $result->fetch_assoc()) 
				{
					$link = $row["url"];
					
					if (check_if_link_is_up_to_date_in_database($mysqli, $link) == false)
					{
						echo "UPDATE LINK IN DATABASE WITH CRAWLING: ".$link."<br><br>";
						crawl($mysqli, $link, "", 0);						
					} 
				}
			}
		}
    }
	else if ($mode == "test")
    {
        #crawl($mysqli, 'https://www.heidenheim.de', "", 2);
		#crawl($mysqli, 'https://www.dhbw-heidenheim.de', "", 21);
		#crawl($mysqli, 'https://de.wikipedia.org/wiki/Rainer_Kuhlen', "", 2);
		#crawl($mysqli, 'https://de.wikipedia.org/wiki/Haushund', "", 2);
		#crawl($mysqli, 'https://www.vdi.de', "", 2);
		#crawl($mysqli, 'https://brockhaus.de/ecs/', "", 2);
		#crawl($mysqli, 'https://op.europa.eu/de/web/eu-vocabularies/concept-scheme/-/resource?uri=http://eurovoc.europa.eu/100141', "", 2);
		#crawl($mysqli, 'https://www.schwaebisch-schwaetza.de', "", 2);
		#crawl($mysqli, 'https://www.slm.uni-hamburg.de/service/medienzentrum/links/linklisten.html', "", 2);
		#crawl($mysqli, 'https://www.dwd.de', "", 2);
		#crawl($mysqli, 'https://de.wikipedia.org/wiki/Liste_der_Hochschulen_in_Deutschland', "", 2);
		#crawl($mysqli, 'https://de.wikipedia.org/wiki/Liste_der_St%C3%A4dte_in_Deutschland', "", 2);
		#crawl($mysqli, 'https://de.wikipedia.org/wiki/Liste_der_Staaten_der_Erde', "", 2);
		#crawl($mysqli, 'https://www.landkreis-dillingen.de', "", 2);
		#crawl($mysqli, 'https://www.spektrum.de', "", 2);
		#crawl($mysqli, 'https://hpi.de', "", 2);
		#crawl($mysqli, 'https://www.deutschlandfunk.de/forschung-aktuell.675.de.html', "", 2);
		#crawl($mysqli, 'https://www.bpb.de', "", 2);
		#crawl($mysqli, 'https://www.bundesregierung.de', "", 2);
		#crawl($mysqli, 'https://www.hochschulverband.de', "", 2);
		#crawl($mysqli, 'https://www.br.de', "", 2);
		#crawl($mysqli, 'https://journalistikon.de/wissenschaftsjournalismus/', "", 2);
		#crawl($mysqli, 'https://www.faz.net', "", 2);
		#crawl($mysqli, 'https://www.tagesschau.de', "", 2);
		#crawl($mysqli, 'https://www.voith.com', "", 2);
		#crawl($mysqli, 'https://de.wikipedia.org/wiki/Kategorie:Liste_(Fachsprache)', "", 2);
		#crawl($mysqli, 'https://de.wikipedia.org/wiki/Kategorie:Wikipedia:Liste', "", 2);
    }
    else
    {
        echo "Error: Crawler mode [".$mode."] not supported.<br>";
    }
?>

	</body>
</html>

<?php
	// close the database connection
	include('close.php');
?>