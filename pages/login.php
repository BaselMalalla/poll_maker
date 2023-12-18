<?php
session_start();
require('../config/connection.php');
$emailRe = "/^[a-z0-9]([\w.])*@[a-z0-9][a-zA-Z]*([.][a-zA-Z]{1,})+$/i";
$passRe = "/^(?=.*[a-z])(?=.*[\-\*#&_.])(?=.*[A-Z])(?=.*[\d])([\w.#\*\-&]){8,}$/";

if (isset($_POST['submit'])) {
  if (preg_match($emailRe, $_POST['email']) && preg_match($passRe, $_POST['ps'])) {
    $email = $_POST['email'];
    $pass = $_POST['ps'];
    $sql = "SELECT * FROM users WHERE email='$email'";
    $rs = $db->query($sql);
    if ($row = $rs->fetch()) {
      if (password_verify($pass, $row[3])) {
        $_SESSION['ID'] = $row[0];
        $_SESSION['NAME'] = $row[1];
        // echo $location;
        header("Location: mainpage.php");
      } else
        echo "Wrong passord or e-mail";
    } else
      echo "Wrong passord or e-mail";
  } else
    echo "Wrong passord or e-mail";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="../css/styles.css">
 
</head>

<body>
    <div class="register-login-container">
        <div class="form-header">Login</div>
            <form method="post">
                <div class="input-group">
                    <input class='form-input' type="text" placeholder="E-mail" name="email" required title="Enter your e-mail" />
                </div>
                <div class="input-group">
                    <input class='form-input' type="password" placeholder="Password" name="ps" required />
                </div>
                <input class='submit-button' type="submit" name="submit"></br></br>
                Not a user yet? <a class="link" href="register.php">Register</a><br>Log in as <a class="link" href="mainpage.php">Guest</a>
            </form>
        
    </div>
</body>

</html>
