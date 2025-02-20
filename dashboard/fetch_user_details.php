<?php
// fetch_user_details.php

if(isset($_GET['email']) && !empty($_GET['email'])) {

    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        $success = false;
        return false;
    }

    // Create connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
        exit;
    }

    $email = $_GET['email'];
    $stmt = $conn->prepare("SELECT fname, lname, email, gender, birthdate, citizenship, mobile, userTypeId FROM user_details WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No user found']);
    }

    $stmt->close();
    $conn->close();
}
?>
