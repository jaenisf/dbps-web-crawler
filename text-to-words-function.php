<?php
	function text_to_words_array_with_count($text) 
	{
		$words = $text;
				
		// ----- remove JavaScript Code -----
		$words = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $words);
		
		// ----- remove HTML TAGs -----
		$words = strip_tags($words);
		$words = preg_replace ('/<[^>]*>/', ' ', $words);
		
		// ----- remove control characters -----
		$words = str_replace("\r", '', $words);    // --- replace with empty space
		$words = str_replace("\n", ' ', $words);   // --- replace with space
		$words = str_replace("\t", ' ', $words);   // --- replace with space
		
		// ----- remove multiple spaces -----
		$words = preg_replace('/\s+/', ' ', $words);
		$words = preg_replace("/&nbsp;/", ' ', $words);
		
		//  ----- replace Umlaute from a, o, u -----
		$words = preg_replace('/ä/', 'ae', $words);
		$words = preg_replace('/ö/', 'oe', $words);
		$words = preg_replace('/ü/', 'ue', $words);
		$words = preg_replace('/Ä/', 'ae', $words);
		$words = preg_replace('/Ö/', 'Oe', $words);
		$words = preg_replace('/Ü/', 'Ue', $words);
		$words = preg_replace('/ß/', 'ss', $words);
		
		$words = preg_replace('/\„/', '', $words);
		$words = preg_replace('/\“/', '', $words);
		$words = preg_replace('/\"/', '', $words);
		$words = preg_replace('/&quot;/', '', $words);
		
		$words = preg_replace('/&hellip;/', '', $words);
		$words = preg_replace('/(™|®|©|&trade;|&reg;|&copy;|&#8482;|&#174;|&#169;)/', '', $words);
		
		//  ----- remove inline ampersands  -----
		$words = str_replace(" & ", ' ', $words);
		$words = str_replace(" &amp; ", ' ', $words);
		
		//  ----- all letters to lower case  -----
		$words = strtolower($words);
		
		//  ----- replace other things  -----
		$words = str_replace("uhr", ' uhr', $words);
		
		$words = str_replace(" * ", ' ', $words);
		$words = str_replace("* ", ' ', $words);
		$words = str_replace(" *", ' ', $words);
		
		$words = str_replace(" : ", ' ', $words);
		$words = str_replace(": ", ' ', $words);
		$words = str_replace(" :", ' ', $words);
		
		$words = str_replace(" - ", ' ', $words);
		$words = str_replace("- ", ' ', $words);
		$words = str_replace(" -", ' ', $words);
		
		$words = str_replace(" : ", ' ', $words);
		$words = str_replace(": ", ' ', $words);
		$words = str_replace(" :", ' ', $words);
		
		$words = str_replace(" | ", ' ', $words);
		$words = str_replace("| ", ' ', $words);
		$words = str_replace(" |", ' ', $words);
		
		$words = str_replace(" / ", ' ', $words);
		$words = str_replace("/ ", ' ', $words);
		$words = str_replace(" /", ' ', $words);
		
		$words = str_replace(" . ", ' ', $words);
		$words = str_replace(". ", ' ', $words);
		$words = str_replace(" .", ' ', $words);
		
		$words = str_replace(" , ", ' ', $words);
		$words = str_replace(", ", ' ', $words);
		$words = str_replace(" ,", ' ', $words);
		
		$words = str_replace(", ", ' ', $words);
		$words = str_replace("! ", ' ', $words);
		$words = str_replace("? ", ' ', $words);
		$words = str_replace(" (", ' ', $words);
		$words = str_replace(") ", ' ', $words);
		$words = str_replace(" {", ' ', $words);
		$words = str_replace("} ", ' ', $words);
		$words = str_replace(" [", ' ', $words);
		$words = str_replace("] ", ' ', $words);
		
		$words = preg_replace('/\"/', '', $words);
		$words = preg_replace('/\'/', '', $words);
		
		$words = str_replace("..", '', $words);
		$words = str_replace("...", '', $words);
		$words = str_replace("…", '', $words);
				
		#$words = preg_replace("/[^a-zA-Z0-9\s`~!@#$%^&*()_+-={}|:;<>?,.\/\"\'\\\[\]]/", '', $words);
		$words = preg_replace("/[^a-zA-Z0-9\s\-]/", '', $words);
		
		$words = explode(' ', trim($words));
		
		foreach ($words as $key => $word) {
			if(strlen(preg_replace("/[^a-zA-Z0-9\s]/", '', $word)) == 0) 
			{
				unset($words[$key]); 
			}
			
			$words[$key] = trim($word);
		}
		
		$words = array_filter($words);
		
		$words = array_count_values($words);
		
		arsort($words);
		
		return $words;
	}
?>