<?php

global $errorMsg, $success;
$errorMsg = "";
$success = true;

// Create a database connection
function createDbConnection() {
    global $errorMsg, $success;

    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        $success = false;
        return null;
    } else {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
            return null;
        }
        return $conn;
    }
}

$conn = createDbConnection();
if (!$conn) {
    echo $errorMsg;
}

$events = [];

// Query the database for all classes and their corresponding tutor names
$sql = "SELECT cl.classId, cl.className, cl.startTime, cl.endTime, cl.price, CONCAT(t.fname, ' ', t.lname) AS tutorFullName 
        FROM classes cl
        INNER JOIN tutors t ON cl.tutorId = t.tutorID";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $start = substr($row['startTime'], 0, 5);
        $end = substr($row['endTime'], 0, 5);
        $events[] = [
            'id' => $row['classId'],
            'title' => $row['className'] . ' - ' . $row['tutorFullName'],
            'start' => $start,
            'end' => $end,
            'price' => $row['price'],
        ];
    }
} else {
    error_log("Query failed: " . $conn->error);
}

header('Content-Type: application/json');
echo json_encode($events);
?>
