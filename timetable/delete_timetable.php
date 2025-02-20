<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../dbConn.php';
include '../classes/get_methods.php';

function deleteStudentEnrollment($userId, $classId)
{
    $conn = null;
    $studentId = getStudentId($userId);
    
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }

    try {
        $stmt = $conn->prepare("
        DELETE FROM student_enrollments  
        WHERE studentId = ? 
        AND classId = ?");

        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("ii", $studentId, $classId);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        $conn->close();

        return $affectedRows;
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
?>
