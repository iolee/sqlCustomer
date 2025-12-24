<?php
$host = 'db'; // Instead of 'localhost'
$user = 'root';
$pass = 'rootpassword';
$dbname = 'my_database_name';

$conn = new mysqli($host, $user, $pass, $dbname);
if (mysqli_connect_errno()) {
die("Database connection failed :" .
mysqli_connect_error() . " (" . mysqli_connect_errno() . ")" );
} //end of if statement
?>
