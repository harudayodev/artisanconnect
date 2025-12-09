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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);

    // Check if the email is already registered
    $stmt = $con->prepare("SELECT id FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Email is already registered. Please use a different email.";
        $stmt->close();
    } else {
        $con->begin_transaction();

        try {
            // Hash the password before storing it
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the accounts table
            $stmt = $con->prepare("INSERT INTO accounts (username, password, email, fname, lname) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $hashedPassword, $email, $fname, $lname);

            if ($stmt->execute()) {
                // Insert into the profile table
                $stmt = $con->prepare("INSERT INTO profile (username, bio, followers, following) VALUES (?, 'n/a', 0, 0)");
                $stmt->bind_param("s", $username);

                if ($stmt->execute()) {
                    $con->commit();
                    echo "<p>Registration successful! You can now log in.</p>";
                    echo "<p>Redirecting to the login page in a bit...</p>";
                    echo "<script>
                            setTimeout(function() {
                                window.location.href = 'Login.html';
                            }, 300);
                          </script>";
                } else {
                    throw new Exception("Error inserting into profile table: " . $stmt->error);
                }
            } else {
                throw new Exception("Error inserting into accounts table: " . $stmt->error);
            }

        } catch (Exception $e) {
            $con->rollback();
            echo "Error: " . $e->getMessage();
        } finally {
            $stmt->close();
        }
    }
}

$con->close();
?>
