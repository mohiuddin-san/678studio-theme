<?php
$host = "localhost";
$db_name = "xb592942_sugamonavishop";
$username = "xb592942_sugamo"; 
$password = "Sugamonavi12345"; 

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>