<?php
require '../dbConn.php';

$input = json_decode(file_get_contents('php://input'), true);
$className = $input['className'] ?? null;

if ($className !== null) {
    $classes = getSpecificClasses($className);
    echo json_encode($classes);
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'No class name provided.']);
}

function getSpecificClasses($className)
{
    $conn = null;
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred with the database connection. Please try again later.";
        return null;
    }

    $stmt = $conn->prepare("SELECT classId, date, startTime, endTime FROM classes WHERE className = ?");
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->bind_param("s", $className);

    if (!$stmt->execute()) {
        die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    $result = $stmt->get_result();

    $classDetails = [];
    while ($row = $result->fetch_assoc()) {
        $classDetails[] = $row;
    }

    $stmt->close();
    $conn->close();

    return $classDetails;
}

?>
