<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../dbConn.php';
include '../classes/get_methods.php';


$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['id']) ? (int) $input['id'] : 0;


$timetable = getTutorTimetable($userId);
echo json_encode($timetable);

function getTutorTimetable($userId)
{
    $conn = null;
    // Get the tutor ID using getTutorId
    $tutorId = getTutorId($userId);

    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }

    try {
        $timetable = array();

        $stmt = $conn->prepare(" 
        SELECT 
            c.classId,
            c.className, 
            c.date, 
            c.startTime, 
            c.endTime, 
            t.fname,
            t.lname
        FROM 
            classes c
        INNER JOIN 
            tutors t ON c.tutorId = t.tutorID
        WHERE 
            c.tutorId = ?
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("i", $tutorId);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $timetable[] = $row;
        }

        $stmt->close();

        return $timetable;
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
