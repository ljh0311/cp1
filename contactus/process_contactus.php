<?php

header('Content-Type: application/json');

$name = $email = $contactno = $message = $subject = $acadLvl = $errorMsg = '';
$success = true;

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Email
if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.<br>";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
}

// Name
if (empty($_POST["name"])) {
    $errorMsg .= "Full name is required.<br>";
    $success = false;
} else {
    $name = sanitize_input($_POST["name"]);
}

// Contact Number
if (empty($_POST["contactno"])) {
    $errorMsg .= "Contact Number is required.<br>";
    $success = false;
} else {
    $contactno = sanitize_input($_POST["contactno"]);
}

// Subject
if (empty($_POST["subject"])) {
    $errorMsg .= "Subject is required.<br>";
    $success = false;
} else {
    $subject = sanitize_input($_POST["subject"]);
}

// Academic Level
if (empty($_POST["acadLvl"])) {
    $errorMsg .= "Academic Level is required.<br>";
    $success = false;
} else {
    $acadLvl = sanitize_input($_POST["acadLvl"]);
}

// Message
if (empty($_POST["message"])) {
    $errorMsg .= "Message is required.<br>";
    $success = false;
} else {
    $message = sanitize_input($_POST["message"]);
}

if ($success) {
    saveMemberToDB();
    echo json_encode(["message" => "Message sent successfully!", "success" => true]);
} else {
    echo json_encode(["message" => "The following input errors were detected:" . $errorMsg, "success" => false]);
}

function saveMemberToDB() {
    global $name, $email, $contactno, $subject, $message, $acadLvl, $errorMsg, $success;

    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.<br>";
        $success = false;
        return;
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    if ($conn->connect_error) {
        $errorMsg = "Connection failed: " . $conn->connect_error . "<br>";
        $success = false;
        return;
    }

    $stmt = $conn->prepare("INSERT INTO contactusform (name, email, contactno, subject, message, acadLvl) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $contactno, $subject, $message, $acadLvl);

    if (!$stmt->execute()) {
        $errorMsg = "Execute failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
        $success = false;
    }

    $stmt->close();
    $conn->close();
}