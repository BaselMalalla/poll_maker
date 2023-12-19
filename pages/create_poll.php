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

// // Increment option count
// if (isset($_POST['add-option-btn'])) {
//     $optionsCount++;
// }

// // Decrement option count
// if (isset($_POST['delete-option-btn']) && $optionsCount > 2) {
//     $optionsCount--;
// }

// // Return option count to initial value (2)
// if (isset($_POST['reset-options-btn'])) {
//     $optionsCount = 2;
// }

// Handle form submissions
if (isset($_POST['create-poll-btn'])) {
    handleFormSubmission();
}

// Function to handle form submissions
function handleFormSubmission()
{
    global $optionsCount, $options, $title, $question, $userId;

    // Get form data
    $title = $_POST['titel'] ?? '';
    $question = $_POST['question'] ?? '';
    // $options = array_map('sanitizeInput', $_POST['options'] ?? []);
    $options = $_POST['options'];
    $isTimed = $_POST['isTimed'] ?? '';
    $endDate = ($isTimed) ? $_POST['endDate'] : null;

    // Validate form data
    if (validateForm($title, $question, $options, $isTimed, $endDate)) {
        // Insert poll into the database
        if (insertPollIntoDatabase($title, $question, $userId, $options, $isTimed, $endDate)) {
            header("Location: index.php");
            exit();
        } else {
            echo "Failed to insert poll into the database.";
        }
    } else {
        echo "Form data validation failed.";
    }
}

// Function to sanitize input
function sanitizeInput($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to validate form data
function validateForm($title, $question, $options, $isTimed, $endDate)
{
    // Add your validation logic here
    // Return true if data is valid, false otherwise
    return true;
}

// Function to insert poll into the database
function insertPollIntoDatabase($title, $question, $userId, $options, $isTimed, $endDate)
{
    try {
        require('../config/connection.php');
        $db->beginTransaction();

        // Insert poll details
        $stmt = $db->prepare("INSERT INTO polls(poll_id, user_id, title, question, end_date, is_timed, is_open) VALUES (null, ?, ?, ?, ?, ?, true)");
        $stmt->execute([$userId, $title, $question, $endDate, $isTimed]);

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
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const options = document.getElementById('options');
            const addOptionButton = document.getElementById('addOption');
            const removeOptionButton = document.getElementById('removeOption');

            addOptionButton.addEventListener('click', () => {
                const inputTags = options.getElementsByTagName('input');
                const newField = document.createElement('input');

                const newOptionNumber = inputTags.length + 1; // Calculate the new option number
                Object.assign(newField, {
                    type: 'text',
                    name: 'options[]',
                    classList: ['options'],
                    placeholder: `Option ${newOptionNumber}`,
                });

                options.appendChild(newField);
                console.log("Element should be appended");
            });

            removeOptionButton.addEventListener('click', () => {
                const inputTags = options.getElementsByTagName('input');
                console.log("Element should be removed");
                if (inputTags.length > 2) {
                    options.removeChild(inputTags[inputTags.length - 1]);
                }
            });

        });
    </script>




</head>

<body>
    <div class="form-container">
        <div class="poll-container">
            <h2>Create New Poll</h2>
            <form method='POST' id="poll-form">
                <input class='input-text' type="text" name="titel" placeholder="Title" required /><br>
                <input class='input-text' type="text" name="question" placeholder="Question" required title="Write your question" /><br><br>

                <div id="options">
                    <input class='input-options' type='text' name='options[$i]' placeholder='Option 1' required />
                    <input class='input-options' type='text' name='options[$i]' placeholder='Option 2' required />
                </div>

                <div class="option-controls">
                    <button class='button-primary' type='button' id='addOption'>Add an option</button>
                    <button class='button-primary' type='button' id='removeOption'>Remove last option</button>
                    <button class='button-primary' type='button' onclick='form.reset()'>Reset</button></br></br>

                </div>

                <div class="expiration-options">
                    <h4>Poll Duration:</h4>
                    <div class="radio-options">
                        <label for="option-open">
                            <input type="radio" id="option-open" name="poll_duration" value="open" checked>
                            Leave it open for now
                        </label>
                        <label for="option-timed">
                            <input type="radio" id="option-timed" name="poll_duration" value="timed">
                            Close at:
                        </label>

                    </div>
                    <div class="timed-options" hidden>
                        <input type="datetime-local" id="endDate" name="endDate" min="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                    <script>
                        const timedOptions = document.querySelector('.timed-options');
                        document.querySelectorAll('input[name="poll_duration"]').forEach(radio => {
                            radio.addEventListener('change', (event) => {
                                timedOptions.hidden = event.target.value !== 'timed';
                            });
                        });
                    </script>
                </div>

                <input class='create-poll-button' type='submit' value='Create Poll' name='create-poll-btn' />
            </form>
        </div>
    </div>
</body>

</html>

<?php include('../includes/footer.php'); ?>