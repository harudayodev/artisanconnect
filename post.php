<?php
session_start(); 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

$DATABASE_HOST = "localhost";
$DATABASE_USER = "root";
$DATABASE_PASS = "";
$DATABASE_NAME = "artisan_connect";

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_errno()) {
    echo json_encode(['success' => false, 'error' => 'Failed to connect to database']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $con->prepare("SELECT username FROM accounts WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_text = isset($_POST['text']) ? trim($_POST['text']) : '';

    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_urls = [];
    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmp_name) {
            $file_name = basename($_FILES['files']['name'][$index]);
            $target_file = $upload_dir . uniqid() . "_" . $file_name;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $file_urls[] = $target_file; 
            }
        }
    }

    $file_urls_serialized = !empty($file_urls) ? serialize($file_urls) : null;

    $stmt = $con->prepare("INSERT INTO post (username, description, image, date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $post_text, $file_urls_serialized);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        echo json_encode(['success' => true, 'file_urls' => $file_urls]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save post']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$con->close();
?>
