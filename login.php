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

if (!isset($_POST['username'], $_POST['password'])) {
    exit('Please fill both the username and password fields!');
}

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($stmt = $con->prepare('SELECT id, password, email FROM accounts WHERE username = ?')) {
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password, $email);
        $stmt->fetch();

        if (($_POST['password'])) {

            $validationCode = bin2hex(random_bytes(3)); 
            $_SESSION['validation_code'] = $validationCode;
            $_SESSION['user_id'] = $id;

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'lstrtinao2nd@gmail.com'; 
                $mail->Password   = 'tgig prod uvmz xdtz'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('your-email@gmail.com', 'ArtisanConnect');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Your Validation Code';
                $mail->Body    = "Your validation code is: <strong>$validationCode</strong>";
                $mail->AltBody = "Your validation code is: $validationCode";

                $mail->send();

                header("Location: validate.php");
                exit();

            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

        } else {
            echo 'Incorrect username and/or password!';
        }
    } else {
        echo 'Incorrect username and/or password!';
    }

    $stmt->close();
} else {
    echo 'Could not prepare statement!';
}
