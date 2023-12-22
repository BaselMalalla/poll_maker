<?php
session_start();
include('../includes/header.php');

if (isset($_GET['poll_id'])) {
    $pollId = $_GET['poll_id'];

    try {
        // Assuming you have a PDO connection established in your connection.php file
        require('../config/connection.php');

        // Fetch poll details
        $query = "SELECT * FROM polls WHERE poll_id = :pollId";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':pollId', $pollId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $poll = $stmt->fetch(PDO::FETCH_ASSOC);
            $title = $poll['title'];
            $question = $poll['question'];
            $endDate = $poll['end_date'];

            // Check if the poll is open
            $isOpen = ($endDate === null || strtotime($endDate) > time()) ? true : false;

            // Fetch options for the poll
            $queryOptions = "SELECT * FROM poll_options WHERE poll_id = :pollId";
            $stmtOptions = $db->prepare($queryOptions);
            $stmtOptions->bindValue(':pollId', $pollId, PDO::PARAM_INT);
            $stmtOptions->execute();

            $options = $stmtOptions->fetchAll(PDO::FETCH_ASSOC);

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
            } else {
                echo "<p class=\"validation-message\">This poll is closed and cannot be voted on.</p>";
            }
        } else {
            echo "<p class=\"validation-message\">Poll not found.</p>";
        }
    } catch (PDOException $e) {
        // Handle the exception (e.g., log or display an error message)
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "<p class=\"validation-message\">Invalid poll ID.</p>";
}

include('../includes/footer.php');
?>