<?php
require('../config/connection.php');

if (isset($_POST['email'])) {
    $email = $_POST['email'];

    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['taken' => $result['count'] > 0]);
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>