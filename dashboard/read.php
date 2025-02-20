<?php
// Check existence of id parameter before processing further
if(isset($_GET["email"]) && !empty(trim($_GET["email"]))){
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
    $stmt = $conn->prepare("SELECT email FROM user_details WHERE email = ?");
    $stmt->bind_param($stmt, "i", $param_id);

    // Set parameters
    $param_id = trim($_GET["email"]);

    // Attempt to execute the prepared statement
    if($stmt->execute()){
        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) == 1){
            /* Fetch result row as an associative array. Since the result set
            contains only one row, we don't need to use while loop */
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            
            // Retrieve individual field value
            $fname = $row["fname"];
            $lname = $row["lname"];
            $email = $row["email"];
            $gender = $row["gender"];
            $birthdate = $row["birthdate"];
            $citizenship = $row["citizenship"];
            $mobile = $row["mobile"];
            $userTypeID = $row["userTypeID"];
        } else{
            // URL doesn't contain valid id parameter. Redirect to error page
            header("location: error.php");
            exit();
        }
        
    } else{
        echo "Oops! Something went wrong. Please try again later.";
    }
    
     
    // Close statement
    mysqli_stmt_close($stmt);
    
    // Close connection
    mysqli_close($link);
} else{
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper{
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="mt-5 mb-3">View Record</h1>
                    <div class="form-group">
                        <label>Name</label>
                        <p><b><?php echo $row["fname"] . $row["lname"]; ?></b></p>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <p><b><?php echo $row["email"]; ?></b></p>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <p><b><?php echo $row["gender"]; ?></b></p>
                    </div>
                    <div class="form-group">
                        <label>Birthdate</label>
                        <p><b><?php echo $row["birthdate"]; ?></b></p>
                    </div>
                    <div class="form-group">
                        <label>Citizenship</label>
                        <p><b><?php echo $row["citizenship"]; ?></b></p>
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <p><b><?php echo $row["mobile"]; ?></b></p>
                    </div>
                    <div class="form-group">
                        <label>User Type ID</label>
                        <p><b><?php echo $row["userTypeID"]; ?></b></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>
