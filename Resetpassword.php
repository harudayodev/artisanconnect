<?php
session_start();

$DATABASE_HOST = "localhost";
$DATABASE_USER = "root";
$DATABASE_PASS = "";
$DATABASE_NAME = "artisan_connect";

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized access. Please try resetting your password again.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        exit('Passwords do not match. Please try again.');
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $user_id = $_SESSION['user_id'];
    $stmt = $con->prepare("UPDATE accounts SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);

    if ($stmt->execute()) {
        session_destroy();
        header("Location: login.html");
        exit('Password reset successfully. You can now log in.');
    } else {
        exit('Failed to update password. Please try again.');
    }

    $stmt->close();
}

$con->close();
?>
