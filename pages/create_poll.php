<?php
session_start();
include('../includes/header.php');

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$userId = $_SESSION['user_id'];
$optionsCount = isset($_POST['optionsCount']) ? intval($_POST['optionsCount']) : 2;
$options = [];
$title = "";
$question = "";
$createPollResponses = [];

// Handle form submissions
if (isset($_POST['create-poll-btn'])) {
    handleFormSubmission();
}

// Function to handle form submissions
function handleFormSubmission()
{
    global $createPollResponses, $options, $title, $question, $userId;

    // Get form data and sanitize inputs
    $title = sanitizeInput($_POST['title'] ?? '');
    $question = sanitizeInput($_POST['question'] ?? '');
    $options = array_map('sanitizeInput', $_POST['options'] ?? []);
    $endDate = ($_POST['poll_duration'] == 'timed') ? sanitizeInput($_POST['endDate']) : null;



    // Validate form data
    $validationMessages = validateForm($title, $question, $options, $endDate);
    if (empty($validationMessages)) {
        // Insert poll into the database
        if (insertPollIntoDatabase($title, $question, $userId, $options,  $endDate)) {
            header("Location: index.php");
            exit();
        } else {
            $validationMessages[] = "Failed to insert poll into the database.";
        }
    } else {
        $validationMessages[] = " Form data validation failed.";
    }
    $createPollResponses = $validationMessages;
}

// Function to sanitize input
function sanitizeInput($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to validate form data
function validateForm($title, $question, $options, $endDate)
{
    $validationMessages = [];

    // Check if title and question are not empty
    if (empty($title) || empty($question)) {
        $validationMessages[] = "Title and question are required.";
    }

    // Check if at least two options are provided
    if (count($options) < 2) {
        $validationMessages[] = "At least two options are required.";
    }

    // Validate each option
    foreach ($options as $option) {
        if (empty($option)) {
            $validationMessages[] = "All options must be filled.";
            break;  // Stop the loop if one option is empty
        }
    }

    // Check if the end date is provided for timed polls
    if ($_POST['poll_duration'] == 'timed' && empty($endDate)) {
        $validationMessages[] = "End date is required for timed polls.";
    }

    // If there are errors, return the array of errors
    // Otherwise, return an empty array indicating validation success
    return $validationMessages;
}


// Function to insert poll into the database
function insertPollIntoDatabase($title, $question, $userId, $options, $endDate)
{
    try {
        require('../config/connection.php');
        $db->beginTransaction();

        // Insert poll details
        $stmt = $db->prepare("INSERT INTO polls(poll_id, user_id, title, question, end_date, is_open) VALUES (null, ?, ?, ?, ?, true)");
        $stmt->execute([$userId, $title, $question, $endDate]);

        // Get last inserted poll ID
        $pollId = $db->lastInsertId();

        // Insert poll options
        $stmt = $db->prepare("INSERT INTO poll_options(option_id, poll_id, content) VALUES (null, ?, ?)");
        foreach ($options as $option) {
            $stmt->execute([$pollId, $option]);
        }

        $db->commit();
        return true;
    } catch (PDOException $e) {
        $db->rollBack();
        echo "Error: " . $e->getMessage();
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Poll</title>
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="../js/create_poll.js"></script>
</head>

<body>
    <div class="container">
        <div class="poll-container text-center">
            <div class="container-header">
                <h2 class="bold">Create a New Poll</h2>
            </div>
            <div id="validation-messages"></div>
            <form method='POST' id="poll-form">
                <div class="input-group">
                    <input class='form-input' type="text" name="title" placeholder="Title" required /><br>
                    <input class='form-input' type="text" name="question" placeholder="Question" required title="Write your question" />
                </div>

                <div id="options" class="input-group">
                    <input class='form-input' type='text' name='options[]' placeholder='Option 1' required />
                    <input class='form-input' type='text' name='options[]' placeholder='Option 2' required />
                </div>

                <div class="option-controls">
                    <button class='button-primary' type='button' id='addOption'>Add Option</button>
                    <button class='button-primary' type='button' id='removeOption'>Remove Option</button>
                    <button class='button-primary' type='button' onclick='form.reset()'>Clear</button></br></br>
                </div>

                <div class="expiration-options">
                    <h3 class="bold">Poll Duration</h3>
                    <div class="radio-options">
                        <label for="option-open">
                            <input type="radio" id="option-open" name="poll_duration" value="open" checked>
                            <span class="radio-label">Leave it open for now</span>
                        </label>
                        <label for="option-timed">
                            <input type="radio" id="option-timed" name="poll_duration" value="timed">
                            <span class="radio-label">Choose an expiry date</span>
                        </label>
                    </div>
                    <div class="timed-options" hidden>
                        <input type="datetime-local" id="endDate" name="endDate" min="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                </div>
                <div class='validation-message'>
                    <?php
                    foreach ($createPollResponses as $response) {
                        echo "<p>$response</p>";
                    } ?>
                </div>
                <input class='button-primary button-submit' type='submit' value='Create Poll' name='create-poll-btn' />
            </form>
        </div>
    </div>
</body>

</html>

<?php include('../includes/footer.php'); ?>