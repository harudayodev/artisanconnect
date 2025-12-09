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
$stmt->bind_result($loggedInUsername);
$stmt->fetch();
$stmt->close();

$stmt = $con->prepare("SELECT username FROM accounts WHERE id != ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$usernames = [];
while ($row = $result->fetch_assoc()) {
    $usernames[] = $row['username'];
}

$user1 = $usernames[0] ?? null;
$user2 = $usernames[1] ?? null;

$stmt = $con->prepare("SELECT username, imagefile FROM profilephoto WHERE username IN (?, ?)");
$stmt->bind_param("ss", $user1, $user2);
$stmt->execute();
$stmt->bind_result($profileUsername, $profilePhoto);

$user1ProfilePhoto = 'Resources/user.png';
$user2ProfilePhoto = 'Resources/user.png';
$user1Username = $user1;
$user2Username = $user2;

while ($stmt->fetch()) {
    if ($profileUsername === $user1) {
        $user1ProfilePhoto = $profilePhoto;
    } elseif ($profileUsername === $user2) {
        $user2ProfilePhoto = $profilePhoto;
    }
}
$stmt->close();

$con->close();

echo "<script>
    var loggedInUsername = '" . $loggedInUsername . "';
    var user1ProfilePhoto = '" . $user1ProfilePhoto . "';
    var user2ProfilePhoto = '" . $user2ProfilePhoto . "';
    var user1Username = '" . $user1Username . "';
    var user2Username = '" . $user2Username . "';
</script>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/messaging.css">
    <title>Message Side</title>
</head>
<body>
    <div class="chat-container">
        <div class="user-list">
            <button class="back-button" onclick="window.location.href='HomePageNewUser.php'">⬅ Return</button>
            <h3>Users</h3>
            <div class="user-item" onclick="openChat('user1')">
                <img src="" alt="profile" class="user-avatar" id="user1-avatar">
                <span class="user-name" id="user1-username">User1</span>
            </div>
            <div class="user-item" onclick="openChat('user2')">
                <img src="" alt="profile" class="user-avatar" id="user2-avatar">
                <span class="user-name" id="user2-username">User2</span>
            </div>
        </div>

        <div class="chat-window">
            <div class="chat-header">
                <h2>Chat with <span id="active-user"> </span></h2>
            </div>
            <div class="chat-messages" id="chat-messages">

            </div>
            <div class="input-area">
                <input type="text" class="input-field" id="input-field" placeholder="Type a message...">
                <button class="send-button" onclick="sendMessage()">Send</button>
            </div>
        </div>

        <div class="user-profile">
            <img src="" alt="profile" class="profile-avatar" id="loggedIn-avatar">
            <a href="Profile.php" class="profile-name" id="loggedIn-username"></a>
        </div>

    </div>

    <script>
        let activeUser = null;

        function openChat(user) {
            activeUser = user;
            let receiverUsername = activeUser === 'user1' ? user1Username : user2Username;
            
            document.getElementById('active-user').textContent = receiverUsername;
            document.getElementById('chat-messages').innerHTML = ''; 
            document.getElementById('input-field').focus();
            
            loadMessages(receiverUsername);

            if (activeUser === 'user1') {
                document.getElementById('loggedIn-avatar').src = user1ProfilePhoto;
                document.getElementById('loggedIn-username').textContent = user1Username;
            } else {
                document.getElementById('loggedIn-avatar').src = user2ProfilePhoto;
                document.getElementById('loggedIn-username').textContent = user2Username;
            }
        }

        function loadMessages(receiverUsername) {
            const chatMessagesContainer = document.getElementById('chat-messages');
            
            fetch('loadMessages.php?receiver=' + receiverUsername)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.messages.forEach(msg => {
                            const messageDiv = document.createElement('div');
                            messageDiv.classList.add('chat-message');
                            messageDiv.classList.add(msg.sender === loggedInUsername ? 'sent' : 'received');
                            messageDiv.innerHTML = `
                                <div class="message-content">${msg.message}</div>
                                <div class="message-timestamp">${msg.date}</div>
                            `;
                            chatMessagesContainer.appendChild(messageDiv);
                        });
                    } else {
                        console.log('Failed to load messages');
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                });
        }

        function sendMessage() {
            const messageText = document.getElementById('input-field').value;
            if (messageText && activeUser) {
                const chatMessagesContainer = document.getElementById('chat-messages');
                
                const newMessage = document.createElement('div');
                newMessage.classList.add('chat-message', 'sent');
                newMessage.innerHTML = `<div class="message-content">${messageText}</div>
                                        <div class="message-timestamp">Just now</div>`;
                chatMessagesContainer.appendChild(newMessage);
                document.getElementById('input-field').value = '';
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;

                const formData = new FormData();
                formData.append('message', messageText);
                formData.append('receiver', activeUser === 'user1' ? user1Username : user2Username);

                fetch('sendMessage.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Message sent and stored:', data);
                    } else {
                        console.error('Error sending message:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                });
            }
        }

        document.getElementById('input-field').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {  // Check if the Enter key is pressed
            event.preventDefault();  // Prevent the default action (optional)
            sendMessage();  // Call the sendMessage function
        }
        });

        document.getElementById("user1-avatar").src = user1ProfilePhoto;
        document.getElementById("user2-avatar").src = user2ProfilePhoto;
        document.getElementById("user1-username").textContent = user1Username;
        document.getElementById("user2-username").textContent = user2Username;
    </script>
</body>
</html>
