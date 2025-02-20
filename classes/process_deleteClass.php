<?php
include "../inc/head.inc.php";
include "../inc/nav.inc.php";

require 'delete_class.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $classId = $_POST["deleteClassId"];

    if (!$classId) {
        http_response_code(400);
        renderMessage('No class ID provided.', true);
    }
    try {
        $success = deleteClass($classId);

        if ($success) {
            renderMessage('Successfully deleted your class!');
            exit;
        } else {
            http_response_code(500);
            renderMessage('An error occurred while deleting the class.', true);
            exit;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        renderMessage('An unexpected error occurred.', true);
        exit;
    }

} else {
    http_response_code(405);
    renderMessage('Method not allowed. Only POST requests are accepted.', true);
    exit;
}
?>