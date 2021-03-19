<?php
	// function to execute a mysqli query
	function query($mysqli, $sql) 
	{		
		echo "<br>";
		
		if ($mysqli->query($sql)) 
		{
			echo "Query: $sql <br> --> Successfully processed!<br>";
		} 
		else
		{
			echo "Query: " . $sql . "\n";
			echo "Errno: " . $mysqli->errno . "\n";
			echo "Error: " . $mysqli->error . "\n";
			exit;
		}
		
		echo "<br>";
	}
?>