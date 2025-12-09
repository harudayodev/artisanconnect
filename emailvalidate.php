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
    header("Location: forgot.html"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredCode = $_POST['validation_code'];

    if ($enteredCode === $_SESSION['validation_code']) {
        unset($_SESSION['validation_code']);

        header("Location: Resetpassword.html");
        exit();
    } else {
        $errorMessage = "Invalid validation code. Please try again.";
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
            <?php
            if (isset($errorMessage)) {
                echo "<p style='color: red;'>$errorMessage</p>";
            }
            ?>
            <input type="submit" value="Validate" class="btn">
        </form>
    </div>
</body>
</html>
