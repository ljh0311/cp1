<?php
include "../inc/head.inc.php";
include "../inc/nav.inc.php";

$email = $errorMsg = "";
$success = true;

require 'update_class.php';

function sanitizeInput($data)
{
    return htmlspecialchars(trim($data));
}

function renderMessage($messages, $isError = false) {
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
}

// check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Capture and sanitize form data
    $classId = $_POST['classId'];
    $className = sanitizeInput($_POST['className']);
    $date = sanitizeInput($_POST['selectDate']);
    $startTime = sanitizeInput($_POST['startTime']);
    $endTime = sanitizeInput($_POST['endTime']);
    $price = sanitizeInput($_POST['price']);
    $capacity = sanitizeInput($_POST['capacity']);

    // Validate inputs
    $errors = [];
    if (empty($className)) {
        $errors[] = "Class name is required.";
    }
    if (empty($date) || !preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
        $errors[] = "Valid date is required.";
    }
    if (empty($startTime) || !preg_match('/\d{2}:\d{2}/', $startTime)) {
        $errors[] = "Valid start time is required.";
    }
    if (empty($endTime) || !preg_match('/\d{2}:\d{2}/', $endTime)) {
        $errors[] = "Valid end time is required, and it must be later than start time.";
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Valid price is required.";
    }
    if (!is_numeric($capacity) || $capacity <= 0) {
        $errors[] = "Valid capacity is required.";
    }

    // if there are no errors then run this section
    if (empty($errors)) {
        $success = updateClass($classId, $className, $date, $startTime, $endTime, $price, $capacity);

        if ($success) {
            renderMessage('Successfully updated your class!');
            exit;
        } else {
            renderMessage('An error occurred while updating the class.', true);
            exit;
        }
    } else {
        renderMessage($errors, true);
        exit;
    }
}
