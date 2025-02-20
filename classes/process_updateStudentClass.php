<?php
require 'update_studentClass.php';

$email = $errorMsg = "";
$success = true;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();

    if (isset($_SESSION['userID'])) {
        $userId = $_SESSION['userID'];
    } else {
        die('User ID is not set in the session.');
    }
}

function sanitizeInput($data)
{
    return htmlspecialchars(trim($data));
}

function renderMessage($messages, $isError = false)
{
    // Determine the message text
    $messageText = is_array($messages) ? implode("\\n", $messages) : $messages;

    // Use JavaScript to show an alert and stay on the same page
    echo "<script type='text/javascript'>";
    echo "alert('{$messageText}');";  // Display the message in an alert box
    if ($isError) {
        echo "document.querySelector('.btn').classList.add('btn-danger');";  // Optionally change button class for errors
    } else {
        echo "document.querySelector('.btn').classList.add('btn-secondary');";  // Or for non-errors
    }
    echo "</script>";

    // Optional: You can still have a button or other elements if needed, or remove this part if not required
    echo '<div style="display: flex; flex-direction: column; align-items: center; text-align: center;">';
    echo '<hr class="border-top my-4">';
    $buttonText = $isError ? "Try Again" : "Go Back";
    echo "<button onclick='history.back()' class='btn'>{$buttonText}</button>";
    echo '</div>';

    include "../inc/footer.inc.php";
}

/*function renderMessage($messages, $isError = false)
{
    $buttonText = $isError ? "Try Again" : "Go Back";
    $buttonClass = $isError ? "btn-danger" : "btn-secondary";
    echo '<div style="display: flex; flex-direction: column; align-items: center; text-align: center;">';
    echo '<hr class="border-top my-4">';
    if (is_array($messages)) {
        echo '<h1>Error occurred</h1>';
        echo '<ul>';
        foreach ($messages as $message) {
            echo "<li>{$message}</li>";
        }
        echo '</ul>';
    } else {
        echo "<h1>{$messages}</h1>";
    }

    echo "<br> <button onclick='history.back()' class='btn {$buttonClass}'>{$buttonText}</button><br>";
    include "../inc/footer.inc.php";
    exit;
}*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prevClassId = sanitizeInput($_POST['studentUpdateClassId']);
    $classId = sanitizeInput($_POST['studentSelectDate']);

    $errors = [];

    if (empty($classId)) {
        $errors[] = "Class Date is required.";
    }

    if (empty($errors)) {
        try {
            $success = updateStudentClass($userId, $classId, $prevClassId);

            
            if ($success) {
                renderMessage('Successfully updated your class!');
                exit;
            } else {
                renderMessage('An error occurred while updating the class.', true);
                exit;
            }
        }catch (Exception $e) {
            error_log($e->getMessage());
            renderMessage("An exception occurred: " . $e->getMessage(), true);
        }

    } else {
        renderMessage($errors, true);
        exit;
    }
}
