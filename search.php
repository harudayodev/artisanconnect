<?php
$DATABASE_HOST = "localhost";
$DATABASE_USER = "root";
$DATABASE_PASS = "";
$DATABASE_NAME = "artisan_connect";

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($con, $_POST['search']);
    $query = "SELECT username FROM accounts WHERE username LIKE '%$search%' LIMIT 10";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<div class="search-item" data-username="' . htmlspecialchars($row['username']) . '">' . htmlspecialchars($row['username']) . '</div>';
        }
    } else {
        echo '<div class="search-item">No results found</div>';
    }
}
?>
