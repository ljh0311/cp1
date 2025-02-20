<?php
require 'get_methods.php';
require_once '../dbConn.php';

function getNextClassDates($classId)
{
    $duration = 4; // in weeks
    $conn = null;
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }

    try {
        $stmt = $conn->prepare("SELECT date FROM classes WHERE classId = ?");

        $stmt->bind_param("i", $classId);

        $stmt->execute();
        $result = $stmt->get_result();
        $classData = $result->fetch_assoc();
        $stmt->close();

        if (!$classData) {
            throw new Exception("No class was found with the given ID.");
        }

        $nextSessionDate = new DateTime($classData['date']);

        $nextClassDates = [];
        for ($i = 0; $i < $duration; $i++) {
            // Add the date to the array
            $nextClassDates[] = $nextSessionDate->format('Y-m-d');
            // Modify $nextSessionDate to the next class date (next week)
            $nextSessionDate->modify('+1 week');
        }

        /*
        FOR DEBUGGING
        echo '<pre>'; // This will make it readable in the browser
        print_r($nextClassDates);
        echo '</pre>';
        */
        return $nextClassDates;
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred while updating the student class. Please try again later.";
        return null;
    } finally {
        if ($conn !== null) {
            $conn->close();
        }
    }
}


function updateStudentClass($userId, $classId, $prevClassId) {
    $studentId = getStudentId($userId);

    if ($studentId === null) {
        throw new Exception('No student ID associated with this user ID.');
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
        $conn->begin_transaction();

        // Update all the rows' classId to the new one first
        $stmt = $conn->prepare("UPDATE enrollments SET classId = ? WHERE studentId = ? AND classId = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("iii", $classId, $studentId, $prevClassId);
        $stmt->execute();

        $nextClassDates = getNextClassDates($classId);

        // Select the enrollment IDs of the rows that need to be updated
        $currentEnrollmentsStmt = $conn->prepare("SELECT enrollmentID FROM enrollments WHERE studentId = ? AND classId = ?");
        if (!$currentEnrollmentsStmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $currentEnrollmentsStmt->bind_param("ii", $studentId, $classId);
        $currentEnrollmentsStmt->execute();

        $enrollmentResult = $currentEnrollmentsStmt->get_result();
        $currentEnrollments = $enrollmentResult->fetch_all(MYSQLI_ASSOC);

        // Update the enrollments with the dates from $nextClassDates
        $updateDateStmt = $conn->prepare("UPDATE enrollments SET lessonDate = ? WHERE enrollmentID = ?");
        $affectedRows = 0;
        foreach ($currentEnrollments as $index => $enrollment) {
            if (isset($nextClassDates[$index])) {
                $updateDateStmt->bind_param("si", $nextClassDates[$index], $enrollment['enrollmentID']);
                $updateDateStmt->execute();
                $affectedRows += $updateDateStmt->affected_rows;
            } else {
                break; // No more dates to update, break the loop
            }
        }

        $conn->commit();
        return $affectedRows;

    } catch (Exception $e) {
        if ($conn !== null) {
            $conn->rollback();
        }
        error_log($e->getMessage());
        echo "An error occurred while updating the student class: " . $e->getMessage();
        return null;

    } finally {
        // Close the statements and the connection in the finally block
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($currentEnrollmentsStmt)) {
            $currentEnrollmentsStmt->close();
        }
        if (isset($updateDateStmt)) {
            $updateDateStmt->close();
        }
        if ($conn !== null) {
            $conn->close();
        }
    }
}

