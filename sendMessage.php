<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$DATABASE_HOST = "localhost";
$DATABASE_USER = "root";
$DATABASE_PASS = "";
$DATABASE_NAME = "artisan_connect";

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_errno()) {
    echo json_encode(['error' => 'Failed to connect to MySQL: ' . mysqli_connect_error()]);
    exit();
}

$user_id = $_SESSION['user_id'];
$message = $_POST['message'] ?? '';
$receiver = $_POST['receiver'] ?? '';

if (empty($message) || empty($receiver)) {
    echo json_encode(['error' => 'Message or receiver missing']);
    exit();
}

$stmt = $con->prepare("SELECT username FROM accounts WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($loggedInUsername);
$stmt->fetch();
$stmt->close();

if ($loggedInUsername) {
    $date = date('Y-m-d H:i:s');
    $stmt = $con->prepare("INSERT INTO message (sender, message, receiver, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $loggedInUsername, $message, $receiver, $date);
    
    if ($stmt->execute()) {
        $messageId = $stmt->insert_id;
        $stmt->close();
        
        echo json_encode([
            'success' => 'Message sent successfully',
            'message' => [
                'id' => $messageId,
                'sender' => $loggedInUsername,
                'message' => $message,
                'receiver' => $receiver,
                'date' => $date
            ]
        ]);
    } else {
        echo json_encode(['error' => 'Failed to send message']);
    }
} else {
    echo json_encode(['error' => 'Invalid user']);
}

$con->close();
?>
