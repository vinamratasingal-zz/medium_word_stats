<?php 
	$con = mysqli_connect("localhost","root"); 
	if (mysqli_connect_errno())
    {
  		echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
	$create_database = "CREATE DATABASE IF NOT EXISTS mediumdb"; 
	if (mysqli_query($con, $create_database)) {
		echo "Database mediumdb created successfully"; 
	} else {
		echo "Error creating database mydb"; 
		mysqli_error($con); 
	}
	$create_table = "CREATE TABLE Entries(PID INT NOT NULL AUTO_INCREMENT,PRIMARY KEY(PID),Entry TEXT,Flesch_Kincaid_Ease FLOAT,Flesch_Kincaid_Grade FLOAT)"; 
	mysqli_select_db($con, "mediumdb"); 
	if(mysqli_query($con, $create_table)) {
		echo "Table created successfully"; 
	} else {
		echo "Error creating table foods"; 
		mysqli_error($con); 
	}
	mysqli_close($con);
?> 