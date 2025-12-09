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
?>

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
    <title>Artisan Connect</title>
    <link rel="stylesheet" href="styles/Homepage.css" />
    <link rel="stylesheet" href="styles/notif.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />
    <script src="https://kit.fontawesome.com/b99e675b6e.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
</head>
<body>
    <header class="navbar">
        <div class="navbar-left">
            <h2>Artisan Connect</h2>
            <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
        </div>
        <form style="width: auto;">
        <div class="search">
        <span class="search-icon material-symbols-outlined">search</span>
        <input id="search-bar" class="search-bar" type="text" placeholder="Search...">
        <div id="search-results" class="search-results"></div>
        </div>
        </form>

        <ul class="nav-links">
            <li><a href="HomepageNewUser.php"><img src="Resources/home.png" alt="home"></a></li>
            <li><a href="Messaging.php"> <img src="Resources/messenger.png" alt="message"></a></li>
            <li>
                <i class="fas fa-bell">
                    <div class="dropdown">
                        <div class="notify_item">
                            <div class="notify_img">
                                <img src="<?php echo $profile_photo; ?>" alt="profile_pic" style="width: 50px">
                            </div>
                            <div class="notify_info">
                                <p>Notification 1<span>Commented</span></p>
                                <span class="notify_time">Time here</span>
                            </div>
                        </div>
                        <div class="notify_item">
                            <div class="notify_img">
                                <img src="<?php echo $profile_photo; ?>" alt="profile_pic" style="width: 50px">
                            </div>
                            <div class="notify_info">
                                <p>Notification 2<span>Liked</span></p>
                                <span class="notify_time">Time here</span>
                            </div>
                        </div>
                        <div class="notify_item">
                            <div class="notify_img">
                                <img src="<?php echo $profile_photo; ?>" alt="profile_pic" style="width: 50px">
                            </div>
                            <div class="notify_info">
                                <p>Notification 3<span>Shared</span></p>
                                <span class="notify_time">Time here</span>
                            </div>
                        </div>
                    </div>
                </i>
            </li>
        </ul>
        <img src="<?php echo $profile_photo; ?>" alt="profile" class="profile" onclick="toggleMenu()">
    </header>
    <div class="sub-menu-wrap" id="subMenu">
        <div class="sub-menu">
            <div class="user-info">
                <a href="ProfileUser.php"><img src="<?php echo $profile_photo; ?>" alt="profile" class="profile" onclick="toggleMenu()"></a>
                <a href="ProfileUser.php"><h2 id="username"><?php echo htmlspecialchars($username); ?></h2></a>
            </div>
            <hr>
            <a href="Settings.html" class="sub-menu-link">
                <img src="Resources/settings.png" alt="settings">
                <p>Settings</p>
                <span>></span>
            </a>
            <a href="Feedback.php" class="sub-menu-link">
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
    <div class="main-feed">
        <div class="post-box">
            <textarea id="post-text" placeholder="What's on your mind?" rows="3"></textarea>
            <div class="button-container">
                <!-- Custom File Input Button -->
                <label for="post-file" class="custom-file-upload">
                    Choose Photo
                </label>
                <input type="file" id="post-file" accept="image/*,video/*" multiple onchange="previewFiles()">
                <button class="post-btn" onclick="submitPost()">Post</button>
            </div>
            <div id="preview-container"></div>
        </div>
        <!-- Example Posts -->
        <div class="post">
            <h3>User 1</h3>
            <p>This is a post content example. Loving the new Artisan Connect!</p>
        </div>
        <div class="post">
            <h3>User 2</h3>
            <p>Just finished working on a new project! Super excited to share it.</p>
        </div>
    </div>
    <script>
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
        
    $(document).ready(function(){
        $(".nav-links .fa-bell").click(function(){
            $(this).find(".dropdown").toggleClass("active");
        });
    });

    let subMenu = document.getElementById("subMenu");

    function toggleMenu() {
        subMenu.classList.toggle("open-menu");
    }

    function previewFiles() {
        const files = document.getElementById('post-file').files;
        const previewContainer = document.getElementById('preview-container');
        previewContainer.innerHTML = ''; // Clear previous previews

        Array.from(files).forEach(file => {
            const reader = new FileReader();
            reader.addEventListener('load', function () {
                const previewElement = document.createElement(file.type.startsWith('image/') ? 'img' : 'video');
                previewElement.src = reader.result;
                previewElement.style.maxWidth = '100%';
                if (file.type.startsWith('video/')) {
                    previewElement.controls = true;
                }
                previewContainer.appendChild(previewElement);
            }, false);
            reader.readAsDataURL(file);
        });
    }

    function submitPost() {
        const postText = document.getElementById('post-text').value;
        const fileInput = document.getElementById('post-file');
        const formData = new FormData();
        
        formData.append('text', postText);

        // Add files to FormData
        Array.from(fileInput.files).forEach(file => {
            formData.append('files[]', file);
        });

        // Post the form data to the server
        fetch('post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Server response:', data); 

                document.getElementById('post-text').value = '';
                document.getElementById('post-file').value = '';
                document.getElementById('preview-container').innerHTML = '';

                addPostToFeed(postText, data.file_urls);
            } else {
                console.error('Upload failed:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function addPostToFeed(text, mediaUrls) {
        const feed = document.querySelector('.main-feed');

        const newPost = document.createElement('div');
        newPost.classList.add('post');
        
        const postContent = `<h3>Your Name</h3><p>${text}</p>`;
        newPost.innerHTML = postContent;

        mediaUrls.forEach(url => {
            const mediaElement = document.createElement(url.endsWith('.mp4') ? 'video' : 'img');
            mediaElement.src = url;
            mediaElement.style.maxWidth = '100%';
            if (url.endsWith('.mp4')) {
                mediaElement.controls = true;
            }
            newPost.appendChild(mediaElement);
        });

        feed.insertBefore(newPost, feed.firstChild); 
    }
    function addPostToFeed(text, mediaUrls) {
    const feed = document.querySelector('.main-feed');

    const newPost = document.createElement('div');
    newPost.classList.add('post');

    const postContent = `<h3>${loggedInUsername}</h3><p>${text}</p>`;
    newPost.innerHTML = postContent;

    if (mediaUrls) {
        mediaUrls.forEach(url => {
            const mediaElement = document.createElement(url.endsWith('.mp4') ? 'video' : 'img');
            mediaElement.src = url;
            mediaElement.style.maxWidth = '100%';
            if (url.endsWith('.mp4')) {
                mediaElement.controls = true;
            }
            newPost.appendChild(mediaElement);
        });
    }

    feed.insertBefore(newPost, feed.firstChild);
}

    </script>
</body>

</html>
