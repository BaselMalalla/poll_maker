<?php
session_start();

$submitResponse = "";

$nameRegex = "/^[a-zA-Z][a-z\sA-Z]+$/";
$emailRegex = "/^[a-zA-Z0-9]([\w.])*@[a-z0-9][a-zA-Z]*([.][a-zA-Z]{1,})+$/";
$passwordRegex = "/^(?=.*[a-z])(?=.*[@.=#$!%*_\-?&^])(?=.*[A-Z])(?=.*[\d])([\w@.=#$!%*_\-?&^]){8,}$/";


try {
    require('../config/connection.php');

    if (isset($_POST['register-btn'])) {
        // Check against regex first
        if (!preg_match($nameRegex, $_POST['name'])) {
            $_SESSION['submitResponse'] = "Invalid username format.";
        } else if (!preg_match($emailRegex, $_POST['email'])) {
            $_SESSION['submitResponse'] = "Invalid email format.";
        } else if (!preg_match($passwordRegex, $_POST['password'])) {
            $_SESSION['submitResponse'] = "Password doesn't meet requirements.";
        } else {
            // Regex checks passed, continue processing
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirmPassword'];

            // Password confirmation
            if ($confirmPassword !== $password) {
                $_SESSION['submitResponse'] = "Passwords must match.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Database interaction
                $db->beginTransaction();

                $sql = "INSERT INTO users (user_id, username, email, password) VALUES (null, :name, :email, :hashedPassword)";
                $stmt = $db->prepare($sql);

                $stmt->bindValue(':name', $name);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':hashedPassword', $hashedPassword);

                $stmt->execute();

                $db->commit();
                header("Location: login.php");
            }
        }
    }
    // Check if there's a session message and clear it
    if (isset($_SESSION['submitResponse'])) {
        $submitResponse = $_SESSION['submitResponse'];
        unset($_SESSION['submitResponse']);
    } else {
        $submitResponse = "";
    }
} catch (PDOException $e) {
    die("Error :" . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <div class="register-login-container">
        <div class="form-header">Register</div>
        <form method="post">
            <div class="input-group">
                <input class='form-input' type="text" placeholder="Username" name="name" required />
                <div class="validation-message" id="username-validation-message"></div>
            </div>
            <div class="input-group">
                <input class='form-input' type="text" placeholder="Email" name="email" required />
                <div class="validation-message" id="email-validation-message"></div>
            </div>
            <div class="input-group">
                <input class='form-input' type="password" placeholder="Create Password" name="password" required />
                <div class="validation-message" id="password-validation-message"></div>
            </div>
            <div class="input-group">
                <input class='form-input' type="password" placeholder="Confirm Password" name="confirmPassword" required />
            </div>
            <input class='submit-button' type="submit" value="Sign Up" name="register-btn" />
            <div class="validation-message">
                <?php echo $submitResponse; ?>
            </div>
            <div class="footer-links">
                <div class="login-actions">
                    Have an account? <a class="link" href="login.php">Login</a>
                </div>
                <div class="login-actions">
                    Or continue as <a class="link" href="mainpage.php">Guest</a>
                </div>
            </div>
        </form>
    </div>
    <script src="../js/validation.js"></script>
</body>

</html>