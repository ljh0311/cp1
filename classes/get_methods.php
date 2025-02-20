<?php
require_once '../dbConn.php';

function getTutorId($userId) {
    $conn = null;

    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }

    try {
        $stmt = $conn->prepare("SELECT tutorID FROM tutors WHERE userId = ?");

        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("i", $userId);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }

        $row = $result->fetch_assoc();
        $tutorId = $row['tutorID'];

        $stmt->close();

        return $tutorId;
    } catch (Exception $e) {

        error_log($e->getMessage());
        echo "An error occurred while updating the class. Please try again later.";
        return null;

    } finally {
        if ($conn !== null) {
            $conn->close();
        }
    }
    
}

function getStudentId($userId) {
    $conn = null;
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }

    try {
        $stmt = $conn->prepare("SELECT studentID FROM students WHERE userId = ?");

        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("i", $userId);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }

        $row = $result->fetch_assoc();
        $studentId = $row['studentID'];

        $stmt->close();

        return $studentId;

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred while updating the class. Please try again later.";
        return null;

    } finally {
        if ($conn !== null) {
            $conn->close();
        }
    }
}
