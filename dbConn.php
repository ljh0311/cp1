<?php
function getDbConnection() {
    // AWS RDS MySQL connection settings
    $servername = "database1.czsa24cac7y5.us-east-1.rds.amazonaws.com";
    $username = "admin";
    $password = "KappyAdmin";
    $dbname = "tutoring_system"; // Assuming this is your database name, adjust if different

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>