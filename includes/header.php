
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<header>
    <nav class="navbar">
        <ul class="nav-list">
            <li><a href="../pages/index.php">Home</a></li>
            <li><a href="../pages/create_poll.php">Create a poll</a></li>
            
            <?php
            if (isset($_SESSION['user_id'])) {
                // User is logged in
             
                echo '<li><a href="../pages/profile.php">Profile</a></li>';
                echo '<li><a href="../pages/logout.php">Logout</a></li>';
                echo '<li class="logged-in-as">Logged in as ' . $_SESSION['username'] . '</li>';
            } else {
                // User is not logged in
                echo '<li><a href="../pages/register.php">Register</a></li>';
                echo '<li><a href="../pages/login.php">Login</a></li>';
                echo '<li class="logged-in-as">Logged in as Guest</li>';
            }
            ?>
        </ul>
    </nav>
</header>