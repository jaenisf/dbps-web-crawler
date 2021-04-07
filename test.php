<?php
	// https://regex101.com/r/YiqKrF/1
	preg_match_all('/href=(\"([^"]+)\"|\'([^\']+)\')/', file_get_contents("https://ard.de"), $links, PREG_PATTERN_ORDER);
	echo "<pre>";
	print_r($links);
	echo "</pre>";
?>