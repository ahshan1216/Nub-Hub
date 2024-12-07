<?php
$servername = "localhost";
$username = "grohonnc_nubhub";
$password = "grohonnc_nubhub";
$dbname = "grohonnc_nubhub";

// Create connection
$connection = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

?>
