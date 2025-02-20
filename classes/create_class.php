<?php
require_once '../dbConn.php';

require 'get_methods.php';

function createClass($className, $userId, $date, $startTime, $endTime, $price, $capacity)
{
    // Get the tutor ID using getTutorId
    $tutorId = getTutorId($userId);
    if ($tutorId === null) {
        die("Tutor ID not found for User ID $userId");
    }
    
    $conn = null;
    
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }


    try {
        $stmt = $conn->prepare(
            "
        INSERT INTO thetuitioncenter.classes
        (className, tutorId, date, startTime, endTime, price, capacity)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("sisssdi", $className, $tutorId, $date, $startTime, $endTime, $price, $capacity);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return $affectedRows;

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }
}
