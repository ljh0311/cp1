<?php
require_once '../dbConn.php';

function deleteClass($classId) {
    $conn = null;
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM classes WHERE classId = ?");

        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("i", $classId);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();

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