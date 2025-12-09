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

    if (!isset($_SESSION['validation_code'], $_SESSION['user_id'])) {
    header("Location: index.php"); 
    exit();

    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $enteredCode = $_POST['validation_code'];

    if ($enteredCode === $_SESSION['validation_code']) {

        $_SESSION['logged_in'] = true;
        $userId = $_SESSION['user_id'];

        if ($stmt = $con->prepare('SELECT username FROM accounts WHERE id = ?')) {

            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->bind_result($username);
            $stmt->fetch();
            $_SESSION['name'] = $username;

            header("Location: HomepageNewUser.php");
            exit();
        } else {
            echo "Error fetching username.";
        }
        } else {
        echo "Invalid validation code";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validate Code</title>
    <link rel="stylesheet" href="styles/validate.css"> 
</head>
<body class="validate">
    <div class="login-box">
        <h2>Enter Validation Code</h2>
        <form action="" method="POST">
            <div class="textbox">
                <input type="text" name="validation_code" placeholder="Enter validation code" required />
            </div>
            <input type="submit" value="Validate" class="btn">
        </form>
    </div>
</body>
</html>
