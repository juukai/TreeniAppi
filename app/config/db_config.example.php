<?php

$servername = "your_server";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";

// Luodaan tietokantayhteys
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Tietokantayhteyden muodostaminen epäonnistui: " . $conn->connect_error);
}

?>
