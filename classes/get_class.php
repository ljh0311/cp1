<?php
require_once '../dbConn.php';

function getClass($classId) {
    $conn = null;
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM classes WHERE classId = ?");

        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("i", $classId);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }
    
        $classDetails = $result->fetch_assoc();
    
        $stmt->close();

        return $classDetails;
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


$input = json_decode(file_get_contents('php://input'), true);
$classId = $input['classId'] ?? null;

if ($classId !== null) {
    $classDetails = getClass($classId);
    echo json_encode($classDetails);
} else {
    http_response_code(400); 
    echo json_encode(['error' => 'No class ID provided.']);
}
?>
