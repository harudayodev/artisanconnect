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
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

$stmt = $con->prepare("SELECT imagefile FROM profilephoto WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($profile_photo);
$stmt->fetch();
$stmt->close();

$profile_photo = $profile_photo ?: 'Resources/user.png';

$con->close();

echo "<script>
    var loggedInUsername = '" . $username . "';
    var loggedInProfilePhoto = '" . $profile_photo . "';
</script>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Connect - Profile</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />
    <link rel="stylesheet" href="styles/profile.css">
    <link rel="stylesheet" href="styles/Homepage.css">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
</head>
<body>
    <header class="navbar">
        <div class="navbar-left">
            <a href="HomepageNewUser.php"><h2>Artisan Connect</h2></a>
            <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
        </div>
        <form>
            <div class="search">
                <span class="search-icon material-symbols-outlined">search</span>
                <input id="search-bar" class="search-bar" type="text" placeholder="Search...">
                <div id="search-results" class="search-results"></div>
            </div>
        </form>
        <ul class="nav-links">
            <li><a href="HomepageNewUser.php"><img src="Resources/home.png" alt="home"></a></li>
            <li><a href="Messaging.php"> <img src="Resources/messenger.png" alt="message"></a></li>
            <li><img src="Resources/bell.png" alt="notification"></li>
        </ul>
        <img src="<?php echo $profile_photo; ?>" alt="profile" class="profile" onclick="toggleMenu()">
    </header>

    <div class="sub-menu-wrap" id="subMenu">
        <div class="sub-menu">
            <div class="user-info">
                <a href="ProfileUser.html"><img src="<?php echo $profile_photo; ?>" alt="profile" class="profile" onclick="toggleMenu()"></a>
                <a href="ProfileUser.html"><h2 id="username-display">User</h2></a>
            </div>
            <hr>
            <a href="Settings.html" class="sub-menu-link">
                <img src="Resources/settings.png" alt="settings">
                <p>Settings</p>
                <span>></span>
            </a>
            <a href="Feedback.html" class="sub-menu-link">
                <img src="Resources/information.png" alt="feedback">
                <p>Feedback</p>
                <span>></span>
            </a>
            <a href="Login.html" class="sub-menu-link">
                <img src="Resources/logout.png" alt="logout">
                <p>Log Out</p>
                <span>></span>
            </a>
        </div>
    </div>

    <div class="wrap">
        <header></header>
        <div class="in_container">
            <div class="left_col">
                <div class="profile">
                    <img src="<?php echo $profile_photo; ?>" alt="profile" class="profile">
                    <span></span>
                </div>
                <h2 id="profile-username">User</h2>
                <p>No info</p>
                <ul class="about">
                    <li><span>0</span>Followers</li>
                    <li><span>0</span>Following</li>
                </ul>
                <div class="content">
                    <p>No bio</p>
                    <ul>
                        <li><a href="" target="_blank"><i class="fab fa-twitter"></i></a></li>
                        <li><a href=""><i class="fab fa-instagram"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="right_col">
                <nav>
                    <ul>
                        <li><a href="#">Photos</a></li>
                        <li><a href="#">About</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script>
        let subMenu = document.getElementById("subMenu");

        function toggleMenu() {
            subMenu.classList.toggle("open-menu");
        }

        $(document).ready(function() {
            $('#search-bar').on('input', function() {
                let query = $(this).val();
                if (query.length > 0) {
                    $.ajax({
                        url: 'search.php',
                        method: 'POST',
                        data: { search: query },
                        success: function(data) {
                            $('#search-results').html(data).show();
                        }
                    });
                } else {
                    $('#search-results').hide();
                }
            });

            $(document).on('click', '.search-item', function() {
                let username = $(this).data('username');
                window.location.href = 'Profile.php?username=' + encodeURIComponent(username);
            });
        });

        document.getElementById("username-display").textContent = loggedInUsername;
        document.getElementById("profile-username").textContent = loggedInUsername;
    </script>
</body>
</html>
