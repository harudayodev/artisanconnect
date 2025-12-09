<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

// Database connection
$DATABASE_HOST = "localhost";
$DATABASE_USER = "root";
$DATABASE_PASS = "";
$DATABASE_NAME = "artisan_connect";

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Get the logged-in user's username
$user_id = $_SESSION['user_id'];
$stmt = $con->prepare("SELECT username FROM accounts WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic = isset($_POST['topic']) ? trim($_POST['topic']) : '';
    $details = isset($_POST['details']) ? trim($_POST['details']) : '';
    $date = date('Y-m-d');

    if (empty($topic)) {
        $error = "Please select a topic.";
    } else {
        // Insert feedback into the database
        $stmt = $con->prepare("INSERT INTO feedback (username, topic, info, date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $topic, $details, $date);

        if ($stmt->execute()) {
            $success = true; 
        } else {
            $error = "Error submitting feedback. Please try again.";
        }

        $stmt->close();
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <link rel="stylesheet" href="styles/feedback.css">
    <script>
        function validateFeedbackForm(event) {
            const topic = document.getElementById("topic").value;
            if (topic === "") {
                event.preventDefault(); // Prevent form submission
                alert("Please choose a topic.");
            }
        }

        // Show a success alert if feedback is submitted successfully
        function showSuccessAlert() {
            alert("Feedback submitted successfully!");
        }
    </script>
</head>
<body onload="<?php echo isset($success) && $success ? 'showSuccessAlert()' : ''; ?>">
    <div class="container">
        <button class="return-button" onclick="window.location.href='HomePageNewUser.php'">← Return</button>
        <div class="feedback-box">
            <h1>Feedback</h1>
            <p>Encountered a problem? Have a suggestion? Let us know!</p>

            <!-- Display feedback messages -->
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <!-- Feedback form -->
            <form method="POST" action="feedback.php" onsubmit="validateFeedbackForm(event)">
                <label for="topic">Please choose a topic:</label>
                <select id="topic" name="topic">
                    <option value="">Select a topic</option>
                    <option value="problem">Report a Problem</option>
                    <option value="suggestion">Suggestion</option>
                    <option value="other">Other</option>
                </select>

                <label for="details">Additional Details (Optional):</label>
                <textarea id="details" name="details" rows="4" placeholder="Enter details here..."></textarea>

                <button type="submit" class="submit-button">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>
