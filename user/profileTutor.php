<!DOCTYPE html>
    <html lang="en">
<?php

    // Include necessary files or configurations
    include "inc/head.inc.php";

/*    // Turn on error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
*/
    // Create database connection.
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

    // Define the user ID you want to retrieve data for
    $userID = 3; // Replace '1' with the actual user ID

    // Query to retrieve user data
    $query = "SELECT fname, lname, email, username, gender, birthdate, mobile , citizenship, street, code, parentname, parentmobile FROM user_details WHERE userID = ?";

    // Prepare the query
    $stmt = $conn->prepare($query);

    // Bind parameters
    $stmt->bind_param("i", $userID);

    // Execute the query
    $stmt->execute();

    // Bind result variables
    $stmt->bind_result($fname, $lname, $email, $username, $gender, $birthdate, $mobile , $citizenship, $street, $code, $parentname, $parentmobile);

    // Fetch the result
    $stmt->fetch();

    // Close the statement
    $stmt->close();

    // Close the connection
    $conn->close();
    ?>



    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Profile</title>
    </head>

    <body>
        <?php
        include "inc/nav.inc.php";
        session_start(); // Start the session if not already started

    ?>


        <section style="background-color: #eee;">
        <form action="user/update_profiletutor.php" method="POST">
            <div class="container py-5">
                <?php
                // Check if a success message is set in the session
                    if (isset($_SESSION['success_message'])) {
                        // Print the success message
                        echo '<div class="alert alert-success" role="alert">' . $_SESSION['success_message'] . '</div>';
                        // Unset the session variable to clear the message
                        unset($_SESSION['success_message']);
                    }

                    // Check if an error message is set in the session
                    if (isset($_SESSION['error_message'])) {
                        // Print the error message
                        echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error_message'] . '</div>';
                        // Unset the session variable to clear the message
                        unset($_SESSION['error_message']);
                    }


                ?>
                <div class="row">
                 <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3.webp" alt="avatar"
                                class="rounded-circle img-fluid" style="width: 150px;">
                            <h5 class="my-3"> <?php echo $fname; ?> <?php echo $lname; ?> </h5>
                            <p class="text-muted mb-1">Tutor</p>
                
                         </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                    <div class="col-sm-3">
                                    <p class="mb-0" style="display: none;">User Id</p>
                                     </div>

                            <div class="col-sm-9">
                                 <input type="hidden" name="userID" value="<?php echo $userID; ?>">
                            </div>                                     
                      
                            <div class="row">
                                <div class="col-sm-3">
                                <p class="mb-0">Full Name</p>
                            </div>
                        
                            <div class="col-sm-9">
                                <input type="text" name="fname" value="<?php echo $fname; ?>">
                                <input type="text" name="lname" value="<?php echo $lname; ?>">
                            </div>
                        </div>
                        
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <p class="mb-0">Email</p>
                            </div>
                        
                            <div class="col-sm-9">
                            <!-- Display the email as a text field -->
                                <div><?php echo $email; ?></div>
                                <input type="hidden" name="email" value="<?php echo $email; ?>">

                            </div>
                        
                        </div>
                        
                        <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Username</p>
                                </div>
                                <div class="col-sm-9">
                                    <!-- Display the email as a text field -->
                                    <div><?php echo $username; ?></div>
                                        <input type="hidden" name="username" value="<?php echo $username; ?>">
                            
                                    </div>
                        
                                </div>
                            </div>
                        <hr>
                       
                        <div class="row">
                             <div class="col-sm-3">
                                <p class="mb-0">Gender</p>
                            </div>
                        
                            <div class="col-sm-9">
                                <input type="text" name="gender" value="<?php echo $gender; ?>">
                        
                            </div>
                        
                        </div>
                        
                        <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Birthdate</p>
                        
                                </div>
                        
                                <div class="col-sm-9">
                                     <input type="date" name="birthdate" value="<?php echo $birthdate; ?>">
                        
                                </div>
                            </div>
                        <hr>
                    
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Mobile</p>
                        
                                </div>
                        
                                <div class="col-sm-9">
                                  <input type="text" name="mobile" value="<?php echo $mobile; ?>">
                        
                                </div>
                            </div>
                        <hr>

                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Citizenship</p>
                        
                                </div>
                        
                                <div class="col-sm-9">
                                 <input type="text" name="citizenship" value="<?php echo $citizenship; ?>">
                        
                                </div>
                            </div>
                        <hr>

     

                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Street</p>
                        
                                </div>
                        
                                <div class="col-sm-9">
                                  <input type="text" name="street" value="<?php echo $street; ?>">
                        
                                </div>
                             </div>
                        <hr>



                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Postal Code</p>
                        
                                </div>
                        
                                <div class="col-sm-9">
                                 <input type="text" name="code" value="<?php echo $code; ?>">
                        
                                </div>
                            </div>
                        <hr>

                        <div class="row">
                                    <div class="col-sm-12 text-center">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                        </div>

                    </div>
                </div>    
                </form>  
            
            </div>  
            
            
        </div>                
       </section>

        <?php
    include "inc/footer.inc.php";
    ?>
    </body>

</html>
