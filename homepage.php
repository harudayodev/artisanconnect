<?php
session_start(); 

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$DATABASE_HOST = "localhost";
$DATABASE_USER = "root";
$DATABASE_PASS = "";
$DATABASE_NAME = "artisan_connect";

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

$stmt = $con->prepare("SELECT username FROM accounts WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

$con->close();

echo "<script>var loggedInUsername = '" . $username . "';</script>";
?>
