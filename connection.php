<?php

foreach (parse_ini_file('.env') as $key => $value) {
  $_ENV[$key] = $value;
}

$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$db_name = $_ENV['DB_NAME'];

try {

    $conn = new PDO("mysql:host=$servername;dbname=$db_name", $username, $password); // connect to the database in phpMyAdmin
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection_status = "Connected successfully"; // Save the connection status as a variable $connection_status, if connection is made in 
  } catch(PDOException $e) {

    $connection_status = "Connection failed: " . $e->getMessage(); // Save the connection status as a variable $connection_status, if connection is not made in
  }
?>
