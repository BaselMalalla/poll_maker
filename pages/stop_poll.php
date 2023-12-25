<?php
session_start();
include('../includes/functions.php');

$pollId = isset($_GET['poll_id']) ? $_GET['poll_id'] : null;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$userId || !$pollId) {
    // Redirect or handle the case where user_id or poll_id is not provided
    header('Location: index.php');
    exit();
}

if (isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $_SESSION['user_id'];
}

// Perform the logic to stop the poll (set end_date to the current time)
try {
    require('../config/connection.php');

    $query = "UPDATE polls SET end_date = DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MINUTE) WHERE poll_id = :pollId AND user_id = :userId";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':pollId', $pollId);
    $stmt->bindValue(':userId', $userId);
    $stmt->execute();

    // Redirect to the home page upon success
    header('Location: index.php');
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
