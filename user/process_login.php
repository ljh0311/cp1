<?php
session_start(); // Start the session at the beginning of the script

error_reporting(E_ALL);
ini_set('display_errors', 1);

$fname = $email = $password = $errorMsg = "";
$userTypeId = -1;
$success = false; // Default to false
$currentSessionID = session_id();

// Helper function to sanitize input
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check login credentials and return user data
function authenticateUser($email, $password, $currentSessionID)
{
    global $errorMsg, $success;

    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        return null;
    }

    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        $errorMsg = "Connection failed: " . $conn->connect_error;
        return null;
    }

    // Prepare the statement
    $stmt = $conn->prepare("
    SELECT u.userId, ud.fname, u.password, u.userTypeId 
    FROM users AS u
    INNER JOIN user_details AS ud ON u.userId = ud.userId
    WHERE u.email = ?");

    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $userId = $row["userId"];
            $fname = $row["fname"];
            $pwd_hashed = $row["password"];
            $userTypeId = $row["userTypeId"];

            // Check if the password matches
            if (password_verify($password, $pwd_hashed)) {
                // Authentication successful, return user data
                $success = True;
                return ['userId' => $userId, 'fname' => $fname, 'userTypeId' => $userTypeId];
            } else {
                $errorMsg = "Login failed";
                echo '<script>alert("Email or password incorrect.");</script>';
                return null;
            }
        } else {
            // Show popup alert for login failure
            echo '<script>alert("Email or password incorrect.");</script>';
            return null;
        }
    } else {
        $errorMsg = "Error executing query";
        echo '<script>alert("Error executing query.");</script>';
        return null;
    }

    $stmt->close();
    $conn->close();
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST["email"]);
    $password = $_POST["pwd"];
    $userData = authenticateUser($email, $password, $currentSessionID);

    if ($userData) {

        // Set session variables
        $_SESSION['userID'] = $userData['userId'];
        $_SESSION['fname'] = $userData['fname'];
        $_SESSION['userTypeId'] = $userData['userTypeId'];

        setcookie("loggedIn", "true", time() + (86400 * 2), "/");
        // Directly use session data for the greeting
        $fname = $_SESSION['fname'];
    }
}

// If no errors, proceed with registration
if (empty($errorMsg)) {
    include "../inc/head.inc.php";
    // Display success message
    include "../inc/nav.inc.php";

    $userTypeId = $_SESSION['userTypeId'];

    switch ($userTypeId) {
        case 1:
            echo '<div style="display: flex; flex-direction: column; align-items: center; text-align: center;">';
            echo '<hr class="border-top my-4">';
            echo "<h1>Welcome back, " . $fname  . "</h1>";
            echo '<br> <a href="../dashboard/dashboard.php" class="btn btn-success">Head to Dashboard</a> <br>';
            break;
        case 2:
            echo '<div style="display: flex; flex-direction: column; align-items: center; text-align: center;">';
            echo '<hr class="border-top my-4">';
            echo "<h1>Welcome back, " . $fname  . "</h1>";
            echo '<br> <a href="../profile.php" class="btn btn-success">Update Profile</a> <br>';
            echo '<br> <a href="../timeTable.php" class="btn btn-success">View Timetable</a> <br>';
            break;
        case 3:
            echo '<div style="display: flex; flex-direction: column; align-items: center; text-align: center;">';
            echo '<hr class="border-top my-4">';
            echo "<h1>Welcome back, " . $fname  . "</h1>";
            echo '<br> <a href="../profileTutor.php" class="btn btn-success">Update Profile</a> <br>';
            echo '<br> <a href="../timeTable.php" class="btn btn-success">View Timetable</a> <br>';
            break;
        default:
            echo '<div style="display: flex; flex-direction: column; align-items: center; text-align: center;">';
            echo '<hr class="border-top my-4">';
            echo "<h1>Oops! An error occurred!</h1>";
            echo '<br> <a href="../login.php" class="btn btn-success">Back to Login</a> <br>';
            break;

    }
    include "../inc/footer.inc.php";
} else {
    // Display errors to the user
    include "../login.php";
}
