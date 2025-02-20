<?php
/*
// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/
// Include necessary files or configurations
include "../inc/head.inc.php";

// Function to sanitize input data
function sanitizeInput($data)
{
    return htmlspecialchars(trim($data));
}

// Create database connection
$config = parse_ini_file('/var/www/private/db-config.ini');
if (!$config) {
    die("Failed to read database config file.");
}

$conn = new mysqli(
    $config['servername'],
    $config['username'],
    $config['password'],
    $config['dbname']
);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Extract and sanitize form data
    $userID = intval(sanitizeInput($_POST["userID"]));
    $fname = isset($_POST["fname"]) && !empty($_POST["fname"]) ? sanitizeInput($_POST["fname"]) : null;
    $lname = isset($_POST["lname"]) && !empty($_POST["lname"]) ? sanitizeInput($_POST["lname"]) : null;
    $email = isset($_POST["email"]) && !empty($_POST["email"]) ? sanitizeInput($_POST["email"]) : null;
    $username = isset($_POST["username"]) && !empty($_POST["username"]) ? sanitizeInput($_POST["username"]) : null;
    $gender = isset($_POST["gender"]) && !empty($_POST["gender"]) ? sanitizeInput($_POST["gender"]) : null;
    $birthdate = isset($_POST["birthdate"]) && !empty($_POST["birthdate"]) ? sanitizeInput($_POST["birthdate"]) : null;
    $mobile = isset($_POST["mobile"]) && !empty($_POST["mobile"]) ? sanitizeInput($_POST["mobile"]) : null;
    $citizenship = isset($_POST["citizenship"]) && !empty($_POST["citizenship"]) ? sanitizeInput($_POST["citizenship"]) : null;
    $street = isset($_POST["street"]) && !empty($_POST["street"]) ? sanitizeInput($_POST["street"]) : null;
    $code = isset($_POST["code"]) && !empty($_POST["code"]) ? sanitizeInput($_POST["code"]) : null;

    // Check if any required fields are not set
    if (
        $fname === null ||
        $lname === null ||
        $gender === null ||
        $birthdate === null ||
        $mobile === null ||
        $citizenship === null ||
        $street === null ||
        $code === null
    ) {
        // If any required fields are not set, redirect to profile page with error message
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: /profileTutor.php");
        exit();
    }

    // Prepare the query to retrieve current database values
    $query = "SELECT fname, lname, email, username, gender, birthdate, mobile, citizenship, street, code FROM user_details WHERE userID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->store_result();
    
    // Bind result variables
    $stmt->bind_result($db_fname, $db_lname, $db_email, $db_username, $db_gender, $db_birthdate, $db_mobile, $db_citizenship, $db_street, $db_code);
    
    // Fetch the result
    $stmt->fetch();
    
    // Check if submitted field values are the same as database values
    if (
        $fname === $db_fname &&
        $lname === $db_lname &&
        $email === $db_email &&
        $username === $db_username &&
        $gender === $db_gender &&
        $birthdate === $db_birthdate &&
        $mobile === $db_mobile &&
        $citizenship === $db_citizenship &&
        $street === $db_street &&
        $code === $db_code
    ) {
        $_SESSION['error_message'] = "No changes were made.";
        header("Location: /profileTutor.php");
        exit();
    }
    
    // Close the statement
    $stmt->close();

    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE user_details SET fname=?, lname=?, email=?, username=?, gender=?, birthdate=?, mobile=?, citizenship=?, street=?, code=? WHERE userID=?");
    $stmt->bind_param("ssssssssssi", $fname, $lname, $email, $username, $gender, $birthdate, $mobile, $citizenship, $street, $code, $userID);

    // Execute the update statement
    if ($stmt->execute()) {
        // Redirect back to the profile page with success message
        $_SESSION['success_message'] = "Your details have been successfully updated!";
        header("Location: /profileTutor.php");
        exit();
    } else {
        // If update fails, handle the error
        header("Location: /profileTutor.php");
        exit();
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>
