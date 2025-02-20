<?php
$email = $errorMsg = $lname = $fname = $pwd = $pwd_confirm = "";
$success = true;

$fname = sanitize_input($_POST["fname"]);

if (empty($_POST["email"]))
{
$errorMsg .= "Email is required.<br>";
$success = false;
}
else
{
$email = sanitize_input($_POST["email"]);
// Additional check to make sure e-mail address is well-formed.
if (!filter_var($email, FILTER_VALIDATE_EMAIL))
{
$errorMsg .= "Invalid email format.";
$success = false;
}
}

//fname
if (empty($_POST["lname"]))
{
$errorMsg .= "Last name is required.<br>";
$success = false;
}
else
{
$lname = sanitize_input($_POST["lname"]);
}

//pwd
if (empty($_POST["pwd"]))
{
$errorMsg .= "Password is required.<br>";
$success = false;
}
else
{
$pwd = sanitize_input($_POST["pwd"]);
}

//pwdconfirm
if (empty($_POST["pwd_confirm"]))
{
$errorMsg .= "Confirm Password is required.<br>";
$success = false;
}
else
{
$pwd_confirm = sanitize_input($_POST["pwd_confirm"]);
}
// Additiional check to see if match
if ($pwd != $pwd_confirm) {
$errorMsg .= "Password does not match";
$success = false;
}





if ($success)
{
saveMemberToDB();
echo "<h4>Registration successful!</h4>";
echo "<p>Email: " . $email;
}
else
{
echo "<h4>The following input errors were detected:</h4>";
echo "<p>" . $errorMsg . "</p>";
}
/*
* Helper function that checks input for malicious or unwanted content.
*/
function sanitize_input($data)
{
$data = trim($data);
$data = stripslashes($data);
$data = htmlspecialchars($data);
return $data;
}

/*
* Helper function to write the member data to the database.
*/
function saveMemberToDB()
{
global $fname, $lname, $email, $pwd_hashed, $errorMsg, $success;

$pwd_hashed = password_hash($pwd, PASSWORD_DEFAULT);

// Create database connection.
$config = parse_ini_file('/var/www/private/db-config.ini');
if (!$config)
{
$errorMsg = "Failed to read database config file.";
$success = false;
}
else
{
$conn = new mysqli(
$config['servername'],
$config['username'],
$config['password'],
$config['dbname']
);
// Check connection
if ($conn->connect_error)
{
$errorMsg = "Connection failed: " . $conn->connect_error;
$success = false;
}
else
{
// Prepare the statement:
$stmt = $conn->prepare("INSERT INTO world_of_pets_members
(fname, lname, email, password) VALUES (?, ?, ?, ?)");
// Bind & execute the query statement:
$stmt->bind_param("ssss", $fname, $lname, $email, $pwd_hashed);
if (!$stmt->execute())
{
$errorMsg = "Execute failed: (" . $stmt->errno . ") " .
$stmt->error;
$success = false;
}
$stmt->close();
}
$conn->close();
}
}




?>
