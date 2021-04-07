<?php
	// Create database connection
	$mysqli = new mysqli('127.0.0.1', 'root', 'password');
	#$mysqli = new mysqli('dbps-web-crawler-mysql-db-do-user-7584574-0.b.db.ondigitalocean.com:25060', 'doadmin', 'h4lh95xgcub2p1mn');

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
