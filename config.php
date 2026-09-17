<?php
$servername = "localhost";
$username = "root";
$password = "";
$db = "exam";
$conn = mysqli_connect($servername, $username, $password);
$connect = mysqli_select_db($conn, $db);

if (!$conn || !$connect) {
    die('Database connection failed. Please contact the administrator.');
}
?>