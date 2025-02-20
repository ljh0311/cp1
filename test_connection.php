<?php
require_once 'dbConn.php';

try {
    $conn = getDbConnection();
    echo "Successfully connected to the database!";
    $conn->close();
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 