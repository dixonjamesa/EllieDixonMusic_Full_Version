<?php
//configed.php
// contains configuration settings

$loginfree = array("Home","News","Videos","EP","Gallery","Press","Gigs","Register");
$loginrequired = array("Site");
$sessionTimeout = 60*60*24;

if( $ipaddr == '::1' ) {
  $servername="localhost:8889";
  $sqluser="root";
  $sqlpass="root";
  $sqldb="eddb1";
  $homeURL="/EllieDixonMusic_Full_Version";
}
else {
  $servername="109.68.38.30";
  $sqluser="DM_18998edadmin";
  $sqlpass="Geth573HD3Fhdq3";
  $sqldb="DM_18998eddb1";
  $homeURL="";
}

function init_dataconnection($servername, $sqldb, $sqluser, $sqlpass)
{
  // Create connection
  $msdb_conn = new mysqli($servername, $sqluser, $sqlpass);
  // Check connection
  if ($msdb_conn->connect_error) {
    //echo 'Connect failed.';
    write_logfile("Connection to server ".$servername." failed: " . $msdb_conn->connect_error);
    return;
  }

  // Create database if it's not already there
  $sql = "CREATE DATABASE IF NOT EXISTS ".$sqldb;
  if ($msdb_conn->query($sql) === TRUE) {
      //echo "Database created/connected successfully. ";
  } else {
      write_logfile("Error creating database: " . $msdb_conn->error);
      return;
  }
  // use this database now...
  $dbcon = $msdb_conn->select_db($sqldb);
  if (!$dbcon ){
    write_logfile("Could not connect to database");
    return;
  }

  // Create table of users if it's not already there...
  $sql = "CREATE TABLE IF NOT EXISTS users (
  userId int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  userFirst varchar(30) NOT NULL,
  userLast varchar(30) NOT NULL,
  userEmail varchar(50) NOT NULL,
  userPass varchar(255) NOT NULL,
  userAuth varchar(10) DEFAULT 'User',
  reg_date TIMESTAMP,
  UNIQUE KEY userEmail (userEmail)
  )";

  if ($msdb_conn->query($sql) === TRUE) {
      //echo "Table of users is OK";
  } else {
      write_logfile( "Error at user table: " . $msdb_conn->error);
      return;
  }

  // Create table for IP visitors if it's not already there...
  $sql = "CREATE TABLE IF NOT EXISTS visitors (
  id int(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip varchar (20),
  city varchar(30),
  region varchar(30),
  country varchar(30),
  host varchar(30),
  date TIMESTAMP,
  robot tinyint(1)
  )";

  if ($msdb_conn->query($sql) === TRUE) {
      //echo "Table of visitors is OK";
  } else {
      write_logfile( "Error at visitor table: " . $msdb_conn->error);
      return;
  }
  // Create table for page hits if it's not already there...
  $sql = "CREATE TABLE IF NOT EXISTS pagehits (
  id int(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip varchar (20),
  url varchar(255) NOT NULL,
  date TIMESTAMP
  )";

  if ($msdb_conn->query($sql) === TRUE) {
      //echo "Table of pagehits is OK";
  } else {
      write_logfile( "Error at pagehits table: " . $msdb_conn->error);
      return;
  }

  return $msdb_conn;
}
