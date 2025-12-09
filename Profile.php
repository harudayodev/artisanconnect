<?php

$DATABASE_HOST = "localhost";
$DATABASE_USER = "root";
$DATABASE_PASS = "";
$DATABASE_NAME = "artisan_connect";

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (isset($_GET['username'])) {
    $searched_username = mysqli_real_escape_string($con, $_GET['username']);
    
    $query = "SELECT p.username, p.bio, p.followers, p.following, pp.imagefile 
              FROM profile p
              LEFT JOIN profilephoto pp ON p.username = pp.username
              WHERE p.username = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $searched_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = htmlspecialchars($user['username']);
        $bio = htmlspecialchars($user['bio']);
        $followers = htmlspecialchars($user['followers']);
        $following = htmlspecialchars($user['following']);
        $profile_image = htmlspecialchars($user['imagefile']); 
    } else {
        $username = "Unknown User";
        $bio = "This user does not exist.";
        $followers = 0;
        $following = 0;
        $profile_image = "Resources/user.png"; 
    }
    $stmt->close();
} else {
    header("Location: HomepageNewUser.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Connect - Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="styles/profile.css">
        <link rel="stylesheet" href="styles/Homepage.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
</head>

<body>
<header class="navbar">
            <div class="navbar-left">
            <a href="HomepageNewUser.php"><h2>Artisan Connect</h2></a>
            </div>
            <form>
        <div class="search">
            <span class="search-icon material-symbols-outlined">search</span>
            <input id="search-bar" class="search-bar" type="text" placeholder="Search...">
            <div id="search-results" class="search-results"></div>
        </div>
    </form>
            <ul class="nav-links">
                <li><a href="Homepage.html"><img src="Resources/home.png" alt="home"></a></li>
                <li><img src="Resources/messenger.png" alt="message"></li>
                <li><img src="Resources/bell.png" alt="notification"></li>
            </ul>
            </div>
            <img src="<?php echo $profile_image; ?>" class="profile" alt="Profile photo of <?php echo $username; ?>" onclick="toggleMenu()">
        </header>

            <div class="sub-menu-wrap" id="subMenu">
                <div class="sub-menu">
                    <div class="user-info">
                        <a href="ProfileUser.php"><img src="<?php echo $profile_image; ?>" alt="Profile photo of <?php echo $username; ?>"> </a>
                        <a href="ProfileUser.php"><h2><?php echo $username; ?></h2></a>
                        
                    </div>
                    <hr>
                    <a href="Settings.html" class="sub-menu-link">
                        <img src="Resources/settings.png" alt="prof">
                        <p>Settings</p>
                        <span>></span>
                    </a>
                    <a href="#" class="sub-menu-link">
                        <img src="Resources/information.png" alt="sett">
                        <p>About</p>
                        <span>></span>
                    </a>
                    <a href="Login.html" class="sub-menu-link">
                        <img src="Resources/logout.png" alt="prof">
                        <p>Log Out</p>
                        <span>></span>
                    </a>
                </div>
            </div> 
        </div>
        </header>
        <div class="wrap">
            <header></header>
            <div class="in_container">
                <div class="left_col">
                    <div class="profile">
                    <img src="<?php echo $profile_image; ?>" alt="Profile photo of <?php echo $username; ?>"> 
                        <span></span>
                    </div>
                    <h2><?php echo $username; ?></h2>
                    <p><?php echo $bio; ?></p>
                    <ul class="about">
                    <li><span><?php echo $followers; ?></span> Followers</li>
                    <li><span><?php echo $following; ?></span> Following</li>
                    </ul>

                    <div class="content">
                    <p>Write any bio here guys</p>

                    <ul>
                        <li><a href="https://x.com/InfoItomiku" target="_blank"><i class="fab fa-twitter"></a></i></li>
                       <!-- <li><i class="fab fa-facebook"></i></li> --> 
                        <li><a href="https://www.instagram.com/itomiku_official?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="><i class="fab fa-instagram"></a></i></li>
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

                    <div class="photos">
                    </div>
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

    function toggleMenu() {
        document.getElementById("subMenu").classList.toggle("open-menu");
    }
    </script>
</body>
</html>
