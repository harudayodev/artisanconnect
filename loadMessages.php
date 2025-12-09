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
$receiver = $_GET['receiver'] ?? '';

if (empty($receiver)) {
    echo json_encode(['error' => 'Receiver not specified']);
    exit();
}

$stmt = $con->prepare("SELECT username FROM accounts WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($loggedInUsername);
$stmt->fetch();
$stmt->close();

if ($loggedInUsername) {
    $stmt = $con->prepare("SELECT sender, message, receiver, date FROM message WHERE 
                           (sender = ? AND receiver = ?) OR (sender = ? AND receiver = ?) ORDER BY date ASC");
    $stmt->bind_param("ssss", $loggedInUsername, $receiver, $receiver, $loggedInUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    $stmt->close();

    echo json_encode(['success' => true, 'messages' => $messages]);
} else {
    echo json_encode(['error' => 'Invalid user']);
}

$con->close();
?>
