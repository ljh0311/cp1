<?php
$email = $errorMsg = "";
$success = true;

require 'create_class.php';

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture and sanitize form data
    $className = sanitizeInput($_POST['class-name']);
    $userId = $_POST['createUserId'];
    $date = sanitizeInput($_POST['class-date']);
    $startTime = sanitizeInput($_POST['start-time']);
    $endTime = sanitizeInput($_POST['end-time']);
    $price = sanitizeInput($_POST['class-price']);
    $capacity = sanitizeInput($_POST['class-capacity']);

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

    
    if (empty($errors)) {
        $success = createClass($className, $userId, $date, $startTime, $endTime, $price, $capacity);

        if ($success) {
            include "../inc/head.inc.php";
            include "../inc/nav.inc.php";

            echo '<div style="display: flex; flex-direction: column; align-items: center; text-align: center;">';
            echo '<hr class="border-top my-4">';
            echo "<h1>Successfully created your class!</h1>";
            echo '<br> <button onclick="history.back()" class="btn btn-secondary">Go Back</button><br>';

            include "../inc/footer.inc.php";
            exit;
        } else {
            include "../inc/head.inc.php";
            include "../inc/nav.inc.php";

            echo '<div style="display: flex; flex-direction: column; align-items: center; text-align: center;">';
            echo '<hr class="border-top my-4">';
            echo "<h1>Oops! Error has occured</h1>";
            echo '<br><button onclick="history.back()" class="btn btn-secondary">Go Back</button> <br>';

            include "../inc/footer.inc.php";
            exit;
        }
    }

    if (!empty($errors)) {

        foreach ($errors as $error) {
            echo $error . "<br>";
        }
    }
}
?>
