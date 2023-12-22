<!-- Page structure needs minor improvements when there are no results  -->
<!-- If vote button is disabled, use title to let the user know why -->
<?php
session_start();
include('../includes/header.php');
include('../includes/functions.php');

$polls = [];

$queryResponseMessage = "";

// Fetch all polls from the database
$stmt = getFilteredQueryStatement();
$stmt->execute();

// Check if there are any polls
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pollId = $row['poll_id'];
        $userId = $row['user_id'];
        $title = $row['title'];
        $question = $row['question'];
        $endDate = $row['end_date'];


        // Check if the poll is closed based on date
        $isOpen = isPollOpen($endDate);

        // Create an associative array for the poll
        $poll = [
            'pollId' => $pollId,
            'userId' => $userId,
            'title' => $title,
            'question' => $question,
            'isOpen' => $isOpen,
            'endDate' => $endDate
        ];

        // Add the poll to the array
        $polls[] = $poll;
    }
} else {
    $queryResponseMessage = "No polls found.";
}


function getFilteredQueryStatement()
{
    $statusFilter = isset($_POST['statusFilter']) ? $_POST['statusFilter'] : 'all';
    $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
    $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
    $ownedPolls = true;

    $userId = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : null;

    // Initial query
    $query = "SELECT * FROM polls";
    $filtersApplied = 0;
    // Apply filters
    if ($statusFilter !== 'all') {
        $query .= " WHERE (end_date " . ($statusFilter == 'open' ? 'IS NULL OR end_date >= CURRENT_TIMESTAMP' : '< CURRENT_TIMESTAMP') . ")";
        $filtersApplied++;
    }

    if ($startDate) {
        $query .= ($statusFilter == 'all' ? " WHERE" : " AND") . " end_date >= :startDate";
        $filtersApplied++;
    }

    if ($endDate) {
        $query .= ($statusFilter == 'all' && !$startDate ? " WHERE" : " AND") . " end_date <= :endDate";
        $filtersApplied++;
    }

    // Add owned polls filter
    if ($ownedPolls && $userId) {
        if ($filtersApplied > 0) {
            $query .= " AND user_id = :userId";
        } else {
            $query .= " WHERE user_id = :userId";
        }
        $filtersApplied = 0;
    }

    try {
        require('../config/connection.php');
        $stmt = $db->prepare($query);

        // Bind dates for date range
        if ($startDate) {
            $stmt->bindValue(':startDate', $startDate);
        }

        if ($endDate) {
            $stmt->bindValue(':endDate', $endDate);
        }

        // Bind user ID for owned polls filter
        if ($ownedPolls && $userId) {
            $stmt->bindValue(':userId', $userId);
        }

        return $stmt;
    } catch (PDOException $e) {
        // Handle the exception (e.g., log or display an error message)
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
    <link rel="stylesheet" href="../css/styles.css">

</head>

<body>
    <div class="page-container">
        <div class="flex-container">
            <div class="flex-item card">
                <h1>My Polls</h1>
                <form class="poll-filter-form" method="post" action="">
                    <div class="filter-container">
                        <div class="filter-row">
                            <div class="filter-item">
                                <label for="statusFilter" class="filter-label">Status:</label>
                                <select name="statusFilter" id="statusFilter" class="filter-select">
                                    <option value="all">All</option>
                                    <option value="open">Open</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>

                            <div class="filter-item">
                                <label for="startDate" class="filter-label">Start Date:</label>
                                <input type="date" name="startDate" id="startDate" class="filter-input">
                            </div>

                            <div class="filter-item">
                                <label for="endDate" class="filter-label">End Date:</label>
                                <input type="date" name="endDate" id="endDate" class="filter-input">
                            </div>
                        </div>

                        <div class="filter-row">
                            <input type="submit" value="Apply Filter" class="button-primary button-card">
                        </div>
                    </div>
                </form>
                <div class="validation-message">
                    <?php if (!isset($_SESSION['user_id'])) : ?>
                        <span> You are in view-only mode. Login to cast your votes.</span><br>
                    <?php endif; ?>
                    <span><?php echo $queryResponseMessage ?></span>
                </div>
            </div>

            <?php


            // Loop through $polls to display each poll
            foreach ($polls as $poll) {
                // Extract poll information from the associative array
                $pollId = $poll['pollId'];
                $title = $poll['title'];
                $question = $poll['question'];
                $endDate = $poll['endDate'];
                $creatorId = $poll['userId'];
                $isOpen = isPollOpen($endDate);


                $userId = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : null;
                $allowVote = isUserAllowedToVote($userId, $pollId, $isOpen);
                $userIsTheCreator = isUserTheCreator($userId, $creatorId);
            ?>

                <div class="flex-item">
                    <div class="card">
                        <div class="card-header">
                            <h2><?php echo $title; ?></h2>
                        </div>
                        <div class="card-body">
                            <p><?php echo substr($question, 0, 70) . (strlen($question) > 70 ? '...' : ''); ?></p>
                            <p> <?php echo ($isOpen ? '<span class="open">Open</span>' : '<span class="closed">Closed</span>'); ?></p>

                            <p>Expiry Date: <?php echo ($endDate !== null ? date('Y-m-d H:i', strtotime($poll['endDate'])) : 'No expiry date set'); ?></p>

                            <div class="card-buttons-container">

                                <?php if ($allowVote) : ?>
                                    <a class="button-primary button-card" href='vote.php?poll_id=<?php echo $pollId; ?>'>Vote</a>
                                <?php else : ?>
                                    <a class="button-primary button-card disabled-link" href='vote.php?poll_id=<?php echo $pollId; ?>'>Vote</a>
                                <?php endif; ?>
                                <a class="button-primary button-card" href='results.php?poll_id=<?php echo $pollId; ?>'>Results</a>
                                <?php if ($userIsTheCreator && $isOpen) : ?>
                                    <a class="button-primary button-card button-card-stop" href='stop_poll.php?poll_id=<?php echo $pollId; ?>'>Stop Poll</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php
            }
            ?>
        </div>
    </div>
</body>

</html>

<?php include('../includes/footer.php'); ?>