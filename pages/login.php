<?php
session_start();
include('../includes/header.php');
include('../includes/functions.php');

$loginResponse = "";

try {
  require('../config/connection.php');

  if (isset($_POST['login-btn'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email=:email";

    // Using prepared statement to prevent SQL injection
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $row['username'];
        header("Location: index.php");
        exit();
      } else {
        $_SESSION['loginResponse'] = "Incorrect email or password";
      }
    } else {
      $_SESSION['loginResponse'] = "Incorrect email or password";
    }
  }
  // Check if there's a session message and clear it
  if (isset($_SESSION['loginResponse'])) {
    $loginResponse = $_SESSION['loginResponse'];
    unset($_SESSION['loginResponse']);
  } else {
    $loginResponse = "";
  }
} catch (PDOException) {
  die("Database Error :" . $e->getMessage());
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
  <div class="container text-center">
    <div class="container-header">
      <h2>Login</h2>
    </div>
    <form method="post">
      <div class="input-group">
        <input class='form-input' type="text" placeholder="E-mail" name="email" required />
      </div>
      <div class="input-group">
        <input class='form-input' type="password" placeholder="Password" name="password" required />
      </div>
      <input class='button-primary button-submit' type="submit" value="Login" name="login-btn">
      <div class="validation-message">
        <?php echo $loginResponse; ?>
      </div>
      <div class="footer-links">
        <div class="login-actions">
          Don't have an account? <a class="link" href="register.php">Register</a>
        </div>
        <div class="login-actions">
          Or continue as <a class="link" href="index.php">Guest</a>
        </div>
      </div>
    </form>
  </div>
</body>
<?php include('../includes/footer.php'); ?><?php include('../includes/footer.php'); ?>

</html>