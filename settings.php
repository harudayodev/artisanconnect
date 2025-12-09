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
    exit('You need to be logged in to access this page.');
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    $stmt = $con->prepare("
        SELECT accounts.fname, accounts.lname, accounts.username, accounts.email, accounts.password, profilephoto.imagefile 
        FROM accounts 
        LEFT JOIN profilephoto ON accounts.username = profilephoto.username 
        WHERE accounts.id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($fname, $lname, $username, $email, $password, $imagefile);
    $stmt->fetch();
    $stmt->close();

    $name = $fname . ' ' . $lname;

    echo json_encode([
        "username" => $username,
        "name" => $name,
        "email" => $email,
        "password" => $password,
        "photo" => $imagefile,
    ]);

} else {

    $username = $_POST['username'];
    $email = $_POST['email'];

if (isset($_POST['new_password']) && isset($_POST['repeat_password'])) {
    $new_password = $_POST['new_password'];
    $repeat_new_password = $_POST['repeat_password'];

    if ($new_password !== $repeat_new_password) {
        echo "New password doesn't match.";
        exit;
    }

    $stmt = $con->prepare("UPDATE accounts SET username = ?, email = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $new_password, $user_id);
} else {
    $stmt = $con->prepare("UPDATE accounts SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
}

if ($stmt->execute()) {
    echo "Settings updated successfully. ";
} else {
    echo "Failed to update settings.";
}
$stmt->close();


    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];

        if (in_array($fileExtension, $allowedExtensions) && $_FILES['photo']['size'] <= 800000) {
            $uploadDir = './uploads/';
            $newFileName = $username . '_profile_' . time() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $stmt = $con->prepare("DELETE FROM profilephoto WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->close();

                $stmt = $con->prepare("INSERT INTO profilephoto (username, imagefile) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $destPath);

                if ($stmt->execute()) {
                    echo "Profile photo updated successfully. ";
                } else {
                    echo "Failed to insert new profile photo in the database. ";
                }
                $stmt->close();
            } else {
                echo "Failed to upload the profile photo. ";
            }
        } else {
            echo "Invalid file type or size. ";
        }
    }

    // Handle photo reset
    if (isset($_POST['reset_photo'])) {
        $stmt = $con->prepare("DELETE FROM profilephoto WHERE username = ?");
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            echo "Profile photo reset successfully.";
        } else {
            echo "Failed to reset profile photo.";
        }
        $stmt->close();
    }
}

$con->close();
?>
