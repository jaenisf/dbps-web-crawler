<?php
	// function to execute a mysqli query
	function query($mysqli, $sql) 
	{		
		echo "<br>";
		
		if ($mysqli->query($sql)) 
		{
			echo "Query: $sql <br> &rArr; <b style='color: green;'><i>Successfully processed!</i></b><br>";
		} 
		else
		{
			echo "Query: " . $sql . "<br>";
			echo "<b style='color: red;'><i>Errno: " . $mysqli->errno . "</i></b><br>";
			echo "<b style='color: red;'><i>Error: " . $mysqli->error . "</i></b><br>";
			//exit;
		}
		
		echo "<br>";
	}
?>