<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../dbConn.php';
include '../classes/get_methods.php';

$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['id']) ? (int) $input['id'] : 0;

$enrollments = getStudentEnrollments($userId);
echo json_encode($enrollments);

function getStudentEnrollments($userId)
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
        $enrollments = array();

        $stmt = $conn->prepare("
        SELECT 
            se.enrollmentID, 
            se.studentId, 
            se.classId, 
            c.className, 
            se.lessonDate, 
            c.startTime, 
            c.endTime, 
            t.fname,
            t.lname
        FROM 
            enrollments se
        INNER JOIN 
            classes c ON se.classId = c.classID
        INNER JOIN 
            tutors t ON c.tutorId = t.tutorID
        WHERE 
            se.studentId = ?
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("i", $studentId);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $row;
        }

        $stmt->close();

        return $enrollments;
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
