<?php
session_start();

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "assessment_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}

// Every page requires login unless it sets $no_auth = true before including this file
if (empty($no_auth) && !isset($_SESSION['username'])) {
  header("Location: /assessment_beginner/login.php");
  exit;
}
?>