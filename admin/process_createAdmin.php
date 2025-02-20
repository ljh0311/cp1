<?php
// Initialize variables
$errorMsg = "";
$username = $password = $email = "";
$userTypeID = 0;
$success = true;

// Sanitize and validate the email
if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.<br>";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
}

// Sanitize and validate the username
if (empty($_POST["username"])) {
    $errorMsg .= "Username is required.<br>";
    $success = false;
} else {
    $username = sanitize_input($_POST["username"]);
}

// Ensure user type ID is provided
if (empty($_POST["userTypeID"])) {
    $errorMsg .= "UserTypeID is required.<br>";
    $success = false;
} else {
    $userTypeID = intval($_POST["userTypeID"]);
}

// Sanitize and validate the password
if (empty($_POST["password"])) {
    $errorMsg .= "Password is required.<br>";
    $success = false;
} else {
    $password = sanitize_input($_POST["password"]);
    $pwd_hashed = password_hash($password, PASSWORD_DEFAULT);
}

// Final output based on success
if ($success) {
    // Save admin to database
    saveAdminToDB($username, $pwd_hashed, $email, $userTypeID);
    
    echo "<div class='alert alert-success'>";
    echo "<h4 class='alert-heading'>Admin was added successfully!</h4>";
    echo "</div>";

    echo "<script>";
    echo "console.log('Admin was added successfully!');";
    echo "</script>";

} else {
    // Output JavaScript code for logging
    echo "<script>";
    echo "console.log('Username:', " . json_encode($username) . ");";
    echo "console.log('Password Hash:', " . json_encode($pwd_hashed) . ");";
    echo "console.log('Email:', " . json_encode($email) . ");";
    echo "console.log('User Type ID:', " . json_encode($userTypeID) . ");";
    echo "</script>";

    echo "<div class='alert alert-danger' role='alert'>";
    echo "<h4 class='alert-heading'>Oops!</h4>";
    echo "<p>The following errors were detected:</p>";
    echo "<hr>";
    echo "<ul>";
    echo "<li>" . $errorMsg . "</li>";
    echo "</ul>";
    echo "</div>";
}

/*
* Helper function that checks input for malicious or unwanted content.
*/
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function saveAdminToDB($username, $pwd_hashed, $email, $userTypeID)
{
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

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO thetuitioncenter.users (username, password, email, userTypeID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $pwd_hashed, $email, $userTypeID);

    if ($stmt->execute()) {
        echo "Admin created successfully!";
    } else {
        die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>
