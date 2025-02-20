<?php
$errorMessage = '';
$success = true;

session_start();

$studentID = $_SESSION['userID'] ?? null;

// Create a database connection
function createDbConnection() {
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMessage = "Failed to read database config file.";
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
            $errorMessage = "Connection failed: " . $conn->connect_error;
            $success = false;
            return null;
        }
        return $conn;
    }
}

// Validate and sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate card number
function validateCardNumber($number) {
    return preg_match('/^[0-9]{16}$/', $number);
}

// Validate cardholder's name
function validateCardHolderName($name) {
    return preg_match('/^[a-zA-Z\s]+$/', $name);
}

// Validate CVC
function validateCVC($cvc) {
    return preg_match('/^[0-9]{3,4}$/', $cvc);
}

// Validate expiry date
function validateExpiryDate($expiryDate) {
    $currentYear = date('Y');
    $currentMonth = date('m');
    list($expYear, $expMonth) = explode('-', $expiryDate);

    return $expYear > $currentYear || ($expYear == $currentYear && $expMonth >= $currentMonth);
}

// Generate a unique ID
function generateUniqueID($conn, $table, $column) {
    do {
        $uniqueID = mt_rand(10000000, 99999999);
        $query = "SELECT COUNT(*) FROM $table WHERE $column = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $uniqueID);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
    } while ($result[0] > 0);
    return $uniqueID;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $selectedClasses = json_decode($_POST['selectedClasses'], true);
    $totalAmount = $_POST['totalAmount'];
    $cardNumber = sanitizeInput($_POST['cardNumber']);
    $cardHolderName = sanitizeInput($_POST['cardHolderName']);
    $expiryDate = sanitizeInput($_POST['expiryDate']);
    $cvc = sanitizeInput($_POST['cvc']);

    if (!validateCardNumber($cardNumber)) {
        $errorMessage = 'Invalid card details.';
        $success = false;
    } elseif (!validateCardHolderName($cardHolderName)) {
        $errorMessage = 'Invalid card details.';
        $success = false;
    } elseif (!validateExpiryDate($expiryDate)) {
        $errorMessage = 'Invalid card details.';
        $success = false;
    } elseif (!validateCVC($cvc)) {
        $errorMessage = 'Invalid card details.';
        $success = false;
    }

    $conn = createDbConnection();
    if (!$conn) {
        $errorMessage = 'Database connection failed.';
        $success = false;
    }

    // If validation or database connection fails, redirect to error page
    if (!$success) {
        header("Location: /error.php?message=" . urlencode($errorMessage));
        exit;
    }

    // Process payment and insert booking and payment information into the database
    try {
        $conn->begin_transaction();
        $bookingID = generateUniqueID($conn, 'bookings', 'bookingConfirmationId');
        $paymentID = generateUniqueID($conn, 'payments', 'paymentId');

        foreach ($selectedClasses as $class) {
            $randomID = generateUniqueID($conn, 'bookings', 'bookingId');
            $price = $class['price'];
            $classID = (int) strtok($class['id'], '-');
            $lessonStartDateTime = date('Y-m-d H:i:s', strtotime($class['startTime']));
            $lessonEndDateTime = date('Y-m-d H:i:s', strtotime($class['endTime']));
            $lessonStartDate = date('Y-m-d', strtotime($lessonStartDateTime));
            $lessonEndDate = date('Y-m-d', strtotime($lessonEndDateTime));

            // Get the tutor id
            $tutorIdQuery = $conn->prepare("SELECT tutorId FROM classes WHERE classId = ?");
            $tutorIdQuery->bind_param("i", $classID);
            $tutorIdQuery->execute();
            $tutorIdResult = $tutorIdQuery->get_result();
            if ($tutorIdRow = $tutorIdResult->fetch_assoc()) {
                $tutorId = $tutorIdRow['tutorId'];
            } else {
                $errorMessage = "Tutor ID not found for the selected class.";
                $success = false;

            }

            // Add to enrollment if not enrolled already
            $stmt = $conn->prepare("SELECT * FROM enrollments WHERE studentId = ? AND classId = ? AND lessonDate = ?");
            $stmt->bind_param("iis", $studentID, $classID, $lessonStartDate);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $stmt = $conn->prepare("INSERT INTO enrollments (studentId, classId, tutorId, lessonDate) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiis", $studentID, $classID, $tutorId, $lessonStartDate);
                if (!$stmt->execute()) {
                    $errorMessage = "Failed to insert enrollment record: " . $stmt->error;
                    $success = false;
                }
            } else {
                $stmt->close();
                $conn->close();
                $errorMessage = 'You have already enrolled in one of the classes. Please try again.' . $stmt->error;
                $success = false;
                header("Location: /error.php?message=" . urlencode($errorMessage));
            }

            $stmt = $conn->prepare("INSERT INTO bookings (bookingId, studentId, classId, lessonDateStart, lessonDateEnd, bookingConfirmationId) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiisss", $randomID, $studentID, $classID, $lessonStartDateTime, $lessonEndDateTime, $bookingID);
            $stmt->execute();


            $stmt = $conn->prepare("INSERT INTO payments (bookingId, amount, paymentDate, paymentConfirmationId) VALUES (?, ?, NOW(), ?)");
            $stmt->bind_param("ids", $randomID, $price, $paymentID);
            $stmt->execute();
        }

        $conn->commit();
        $stmt->close();
        $conn->close();
        header("Location: /success.php?message=" . urlencode("Payment successful and booking confirmed."));
    } catch (Exception $e) {
        $conn->rollback();
        $errorMessage = 'Something went wrong while processing the data Please try again.';
        $stmt->close();
        $conn->close();
        $success = false;
        header("Location: /error.php?message=" . urlencode($errorMessage));
    }

} else {
    // Redirect to the home page or form page if not a POST request
    header("Location: index.php");
}
?>
