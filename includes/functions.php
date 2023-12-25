<?php

function sanitizeInput($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function isUserAllowedToVote($userId, $pollId, $isPollOpen)
{
    try {

        require('../config/connection.php');
        // Check if the user is logged in
        if (!$userId || !$isPollOpen) {
            return false;
        }

        // Check if the user has already voted in this poll
        $query = "SELECT COUNT(*) FROM votes WHERE user_id = :userId AND poll_id = :pollId";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':pollId', $pollId, PDO::PARAM_INT);
        $stmt->execute();

        $voteCount = $stmt->fetchColumn();

        return ($voteCount == 0); // User is allowed to vote if they haven't voted before
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function isPollOpen($endDate)
{
    if ($endDate == null) {
        return true;
    }

    $inputTimestamp = strtotime($endDate);

    // Get the current timestamp
    $currentTimestamp = time();

    // Compare the two timestamps adjust for GMT+3 difference
    if ($inputTimestamp - 60 * 60 * 2 > $currentTimestamp) {
        return true; // Date is in the past
    } else {
        return false; // Date is in the future or is the current date
    }
}

function isUserTheCreator($userId, $creatorUserId)
{
    return $userId == $creatorUserId;
}
