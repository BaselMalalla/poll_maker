<?php
session_start();
include('../includes/header.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
    <link rel="stylesheet" href="../css/styles.css">
 
</head>

<body>
    <h1>This is the home page</h1>
    <a class="link" href="register.php">Register</a><br>
    <a class="link" href="login.php">Login</a>
</body>

</html>

<?php include('../includes/footer.php'); ?>