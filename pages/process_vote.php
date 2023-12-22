<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $pollId = $_POST['poll_id'];
        $optionId = $_POST['option_id'];
        $recordedAt = date('Y-m-d H:i:s');

        try {
            // Assuming you have a PDO connection established in your connection.php file
            require('../config/connection.php');

            // Insert vote into the votes table
            $query = "INSERT INTO votes (user_id, poll_id, option_id, recorded_at) VALUES (:userId, :pollId, :optionId, :recordedAt)";
            $stmt = $db->prepare($query);

            $stmt->bindValue(':userId', $userId);
            $stmt->bindValue(':pollId', $pollId);
            $stmt->bindValue(':optionId', $optionId);
            $stmt->bindValue(':recordedAt', $recordedAt);

            $stmt->execute();

            header("Location: results.php?poll_id=$pollId");
        } catch (PDOException $e) {
            // Handle the exception (e.g., log or display an error message)
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "User not authenticated. Please log in to vote.";
    }
} else {
    echo "Invalid request method.";
}
