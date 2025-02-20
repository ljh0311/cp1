<?php
// Turn on error reporting
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/
include "../inc/head.inc.php";


$email = $errorMsg = "";
$success = true;


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Include any necessary files or configurations

    // Function to sanitize input data

    function sanitizeInput($data)
    {
        return htmlspecialchars(trim($data));
    }

    // Function to validate email format
    function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Function to hash the password
    function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Function to validate password strength
    function isValidPassword($password)
    {
        // Check if password length is at least 12 characters
        if (strlen($password) < 12) {
            return false;
        }

        // Check if password contains at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Check if password contains at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Check if password contains at least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Check if password contains at least one symbol
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }

        // Password meets all criteria
        return true;
    }
    
    // Function to check if username already exists
    function isUsernameExists($username)
    {
        global $errorMsg, $success;

        // Create database connection
        $config = parse_ini_file('/var/www/private/db-config.ini');
        if (!$config) {
            $errorMsg = "Failed to read database config file.";
            $success = false;
            return false;
        }

        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
            return false;
        }

        // Prepare the statement
        $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);

        // Execute the query
        $stmt->execute();

        // Store the result
        $stmt->store_result();

        // Check if the username exists
        $usernameExists = $stmt->num_rows > 0;

        $stmt->close();
        $conn->close();

        return $usernameExists;
    }

    // Function to check if email already exists
    function isEmailExists($email)
    {
        global $errorMsg, $success;

        // Create database connection
        $config = parse_ini_file('/var/www/private/db-config.ini');
        if (!$config) {
            $errorMsg = "Failed to read database config file.";
            $success = false;
            return false;
        }

        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
            return false;
        }

        // Prepare the statement
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);

        // Execute the query
        $stmt->execute();

        // Store the result
        $stmt->store_result();

        // Check if the email exists
        $emailExists = $stmt->num_rows > 0;

        $stmt->close();
        $conn->close();

        return $emailExists;
    }

    // Extract and sanitize form data
    $fname = sanitizeInput($_POST["fname"]);
    $lname = sanitizeInput($_POST["lname"]);
    $email = sanitizeInput($_POST["email"]);
    $username = sanitizeInput($_POST["username"]);
    $pwd = sanitizeInput($_POST["pwd"]);
    $pwd_confirm = sanitizeInput($_POST["pwd_confirm"]);
    $userTypeID_Register = 2;
 
    // Perform server-side validation
    $errors = [];

    // Check if required fields are provided
    if (empty($lname) || empty($email) || empty($username) || empty($pwd) || empty($userTypeID_Register) || empty($pwd_confirm)) {
        $errors[] = "All required fields must be filled.";
    } 
    if (!isValidEmail($email)) {
        $errors[] = "Invalid email format.";
    } 
    if ($pwd !== $pwd_confirm) {
        $errors[] = "Password and confirmation password do not match.";
    } 
    if (isEmailExists($email)) {
        $errors[] = "This email address is already registered.";
    }

    if (isUsernameExists($username)) {
        $errors[] = "This username is already taken.";
    }

    if (!isValidPassword($pwd)) {
        $errors[] = "Password must be at least 12 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one symbol.";
    } 

    // Check if username contains spaces
    if (strpos($username, ' ') !== false) {
        $errors[] = "Username cannot contain spaces.";
    }

    // Check if username contains special characters
    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $errors[] = "Username can only contain alphanumeric characters.";
    }

    if (!empty($errors)) {
        // Store errors in session
        session_start();
        $_SESSION['errors'][] = $errors; // Store errors as a nested array
        
        // Redirect to register.php
        header("Location: ../register.php");
        exit();
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        include "../inc/head.inc.php";
        // Hash the password
        $hashedPwd = hashPassword($pwd);

        $success = saveMemberToDB($userTypeID_Register);

        // Display success message
        include "../inc/nav.inc.php";

        echo '<div style="display: flex; flex-direction: column; align-items: center; text-align: center;">';
        echo '<hr class="border-top my-4">';
        echo "<h1>Your Registration is successful!</h1>";
        echo "<p>Thank you for signing up, " . $fname . " " . $lname . "</p>";
        echo '<br> <a href="../login.php" class="btn btn-success">Login</a> <br>';

        include "../inc/footer.inc.php";
    
    } else {
        // Display errors to the user
        include "../register.php";
    }
}

/*
 * Helper function to write the member data to the database.
 */
function saveMemberToDB($userTypeID_Register)
{
    global  $fname, $lname , $email, $username, $userTypeID_Register , $hashedPwd, $errorMsg, $success;
 
    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        return;
    }

    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        $errorMsg = "Connection failed: " . $conn->connect_error;
        return;
    }

    // Start transaction
    $conn->begin_transaction();

    // Prepare the statement to insert user data
    $stmt_users = $conn->prepare("INSERT INTO users(username, password, email, userTypeID) VALUES (?, ?, ?, ?)");
    $stmt_users->bind_param("sssi", $username, $hashedPwd, $email, $userTypeID_Register);


    if (!$stmt_users->execute()) {
        $errorMsg = "Execute failed: (" . $stmt_users->errno . ") " . $stmt_users->error;
        $stmt_users->close();
        $conn->rollback();
        $conn->close();
        return;
    }

    $userId = $conn->insert_id;
    $stmt_users->close();


    // Prepare the statement to insert user details
    $stmt_user_details = $conn->prepare("INSERT INTO user_details(userId, fname, lname, email, username, userTypeId) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_user_details->bind_param("issssi", $userId, $fname, $lname, $email, $username, $userTypeID_Register);

    if (!$stmt_user_details->execute()) {
        $errorMsg = "Execute failed: (" . $stmt_user_details->errno . ") " . $stmt_user_details->error;
        $stmt_user_details->close();
        $conn->rollback();
        $conn->close();
        return;
    }
    $stmt_user_details->close();



    // Insert data into student or tutor table based on userTypeID
    if ($userTypeID_Register == 2) { // Assuming 2 is the ID for students
        $stmt_student = $conn->prepare("INSERT INTO students(userId, fname, lname) VALUES (?, ?, ?)");
        $stmt_student->bind_param("iss", $userId, $fname, $lname);
        if (!$stmt_student->execute()) {
            $errorMsg = "Execute failed: (" . $stmt_student->errno . ") " . $stmt_student->error;
            $stmt_student->close();
            $conn->rollback();
            $conn->close();
            return;
        }
        $stmt_student->close();
    } elseif ($userTypeID_Register == 3) { // Assuming 3 is the ID for tutors
        $stmt_tutor = $conn->prepare("INSERT INTO tutors(userId, fname, lname) VALUES (?, ?, ?)");
        $stmt_tutor->bind_param("iss", $userId, $fname, $lname);
        if (!$stmt_tutor->execute()) {
            $errorMsg = "Execute failed: (" . $stmt_tutor->errno . ") " . $stmt_tutor->error;
            $stmt_tutor->close();
            $conn->rollback();
            $conn->close();
            return;
        }
        $stmt_tutor->close();
    }

    

    // If we reach this point, it means everything went well
    $conn->commit();
    $success = true;
    $conn->close();
}

?>
