<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$config = parse_ini_file('/var/www/private/db-config.ini');
if (!$config) {
    echo json_encode(['error' => "Failed to read database config file."]);
    exit;
}

$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

$email = isset($_POST['email']) ? $_POST['email'] : null;

if ($email) {
    $conn->begin_transaction();
    try {
        // Get the user type ID
        $stmt = $conn->prepare("SELECT userTypeId FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($userTypeID);
        $stmt->fetch();
        $stmt->close();

        // Check if we have a valid userTypeId
        if (isset($userTypeID)) {
            // Disable foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS=0;");

            // Delete user details first (assuming `users` table has a foreign key reference to `user_details`)
            $stmt_details = $conn->prepare("DELETE FROM user_details WHERE email = ?");
            $stmt_details->bind_param("s", $email);
            $stmt_details->execute();
            $stmt_details->close();

            // Delete from student or tutor table based on userTypeID
            if ($userTypeID == 2) {
                // Assuming 2 is the ID for students
                $stmt_student = $conn->prepare("DELETE FROM students WHERE userId = (SELECT userId FROM users WHERE email = ?)");
                $stmt_student->bind_param("s", $email);
                $stmt_student->execute();
                $stmt_student->close();
            } elseif ($userTypeID == 3) {
                // Assuming 3 is the ID for tutors
                $stmt_tutor = $conn->prepare("DELETE FROM tutors WHERE userId = (SELECT userId FROM users WHERE email = ?)");
                $stmt_tutor->bind_param("s", $email);
                $stmt_tutor->execute();
                $stmt_tutor->close();
            }

            // Finally delete the user
            $stmt_user = $conn->prepare("DELETE FROM users WHERE email = ?");
            $stmt_user->bind_param("s", $email);
            $stmt_user->execute();
            $stmt_user->close();

            // Re-enable foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS=1;");

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
        } else {
            throw new Exception('User not found.');
        }
    } catch (Exception $e) {
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=1;");

        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No email provided.']);
}

$conn->close();
?>
