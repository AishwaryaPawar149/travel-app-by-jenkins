<?php
$servername = "database-1.cdwkuiksmrsm.ap-south-1.rds.amazonaws.com";
$username = "root";
$password = "aishwarya149";
$dbname = "travelapp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
