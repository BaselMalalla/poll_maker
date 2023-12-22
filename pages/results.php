<?php
session_start();
include('../includes/header.php');


// Get poll ID from the query parameters
$pollId = isset($_GET['poll_id']) ? $_GET['poll_id'] : null;

if (!$pollId) {
    die("Poll ID is not set or invalid.");
}

try {

    require('../config/connection.php');

    // Fetch poll information
    $query = "SELECT * FROM polls WHERE poll_id = :pollId";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':pollId', $pollId);
    $stmt->execute();

    $poll = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the poll exists
    if (!$poll) {
        echo "Poll not found.";
        exit;
    }

    // Fetch options and vote counts
    $query = "SELECT poll_options.option_id, poll_options.content, COUNT(votes.vote_id) AS vote_count
          FROM poll_options
          LEFT JOIN votes ON poll_options.option_id = votes.option_id
          WHERE poll_options.poll_id = :pollId
          GROUP BY poll_options.option_id, poll_options.content";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':pollId', $pollId);
    $stmt->execute();

    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle the exception (e.g., log or display an error message)
    echo "Error: " . $e->getMessage();
}
function formatResult($voteCount, $totalVotes)
{
    if ($totalVotes > 0) {
        $percentage = ($voteCount / $totalVotes) * 100;
        return sprintf("%.2f%% (%d votes)", $percentage, $voteCount);
    } else {
        return "0.00% (0 votes)";
    }
}


$totalVotes = 0;
foreach ($options as $option) {
    $totalVotes += $option['vote_count'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll Results</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <div class="page-container">
        <h1>Poll Results: <?php echo $poll['title']; ?></h1>
        <div class="poll-results">
            <h2><?php echo $poll['question']; ?></h2>
            <?php foreach ($options as $option) : ?>
                <div class="result-option">
                    <p><?php echo $option['content']; ?></p>
                    <p><?php echo formatResult($option['vote_count'], $totalVotes); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="button-primary" href="index.php">Back to Polls</a>
    </div>
</body>

</html>

<?php include('../includes/footer.php'); ?>