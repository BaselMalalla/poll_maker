<?php
session_start();
include('../includes/header.php');
include('../includes/functions.php');

$pollId = (isset($_GET['poll_id'])) ? $_GET['poll_id'] : null;
$userId = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : null;



if (!$userId || !$pollId) {
    // Redirect to login or home page if the user is not logged in or poll_id is not provided
    header('Location: index.php');
    exit();
}

try {
    require('../config/connection.php');

    // Fetch poll details
    $poll = getPollDetails($db, $pollId);

    if ($poll) {
        $title = $poll['title'];
        $question = $poll['question'];
        $endDate = $poll['end_date'];

        // Check if the poll is open
        $isOpen = isPollOpen($endDate);

        // Fetch options for the poll
        $options = getPollOptions($db, $pollId);
    }
} catch (PDOException $e) {
    // Handle the exception (e.g., log or display an error message)
    echo "Error: " . $e->getMessage();
}
function getPollDetails($db, $pollId)
{
    $query = "SELECT * FROM polls WHERE poll_id = :pollId";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':pollId', $pollId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPollOptions($db, $pollId)
{
    $query = "SELECT * FROM poll_options WHERE poll_id = :pollId";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':pollId', $pollId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Display the voting form
if ($isOpen) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vote - <?php echo $title; ?></title>
        <link rel="stylesheet" href="../css/styles.css">
    </head>

    <body>
        <div class="page-container">
            <h1>Vote - <?php echo $title; ?></h1>
            <p><?php echo $question; ?></p>
            <form method="post" action="process_vote.php">
                <input type="hidden" name="poll_id" value="<?php echo $pollId; ?>">
                <?php foreach ($options as $option) : ?>
                    <div class="radio-options">
                        <label>
                            <input type="radio" name="option_id" value="<?php echo $option['option_id']; ?>">
                            <span class="radio-label"><?php echo $option['content']; ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="button-primary">Vote</button>
            </form>
        </div>
    </body>

    </html>
<?php
}
include('../includes/footer.php');
?>