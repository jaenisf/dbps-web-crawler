<?php
	// Create database connection
	$mysqli = new mysqli('127.0.0.1', 'root', '');

	if ($mysqli->connect_errno) 
	{
		echo "Error: MySQL connection failed. \n";
		echo "Errno: " . $mysqli->connect_errno . "\n";
		echo "Error: " . $mysqli->connect_error . "\n";
		exit;
	} 
	else 
	{
		echo "Connection to the database server was successful! <br>";
	}
	
	echo "<br><hr><br>";
?>
