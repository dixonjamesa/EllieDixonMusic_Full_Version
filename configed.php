<?php
//configed.php

/*$servername="localhost";
$port=":8889";
$sqluser="root";
$sqlpass="root";
$sqldb="eddb1";*/
$servername="109.68.38.30";
$port="";
$sqluser="DM_18998edadmin";
$sqlpass="Geth573HD3Fhdq3";
$sqldb="DM_18998eddb1";

// Create connection
$conn = new mysqli($servername.$port, $sqluser, $sqlpass);
// Check connection
if ($conn->connect_error) {
  echo 'Connect failed.';
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it's not already there
$sql = "CREATE DATABASE IF NOT EXISTS ".$sqldb;
if ($conn->query($sql) === TRUE) {
    //echo "Database created/connected successfully. ";
} else {
    die("Error creating database: " . $conn->error);
}
// use this database now...
$dbcon = $conn->select_db($sqldb);
if (!$dbcon ){
  die("Could not connect to database");
}

// Create table if it's not already there...
$sql = "CREATE TABLE IF NOT EXISTS users (
userId int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
userFirst varchar(30) NOT NULL,
userLast varchar(30) NOT NULL,
userEmail varchar(50),
userPass varchar(255) NOT NULL,
reg_date TIMESTAMP,
UNIQUE KEY userEmail (userEmail)
)";

if ($conn->query($sql) === TRUE) {
    //echo "Table of users is OK";
} else {
    die( "Error creating table: " . $conn->error);
}
