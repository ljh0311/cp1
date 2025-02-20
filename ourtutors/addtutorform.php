<?php
session_start();

/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/

include "../inc/head.inc.php";

$email = $success = $successMessage = "";


include '../tutors_functions.php';

// Assuming tutors are stored in a JSON file named 'tutors.json'
$tutorsFile = '../tutors.json';
$tutors = readTutorsFromFile($tutorsFile);

// Define allowed file types and maximum file size
$allowedFileTypes = array('image/jpeg', 'image/png', 'image/gif');
$maxFileSize = 5 * 1024 * 1024; // 5 MB

// Check if the form was submitted to remove a tutor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_tutor'])) {
    $tutorIndex = $_POST['remove_tutor'];
    
    // Check if the tutor index is valid
    if (isset($tutors[$tutorIndex])) {
        // Remove the tutor from the array
        unset($tutors[$tutorIndex]);

        // Rewrite the updated array back to the file
        writeTutorsToFile($tutorsFile, array_values($tutors));
    }
}

// Handle form submission to add a new tutor
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uploadDirectory = "ourtutors/";
    
    // Check if the directory exists, if not, create it
    if (!file_exists($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true); // Create the directory with full permissions
    }
    
    // Move uploaded image to the 'uploads' directory after validating file type and size
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
        $uploadFile = $_FILES["image"]["tmp_name"];
        $fileType = $_FILES["image"]["type"];
        $fileSize = $_FILES["image"]["size"];

        // Check if file type and size are valid
        if (in_array($fileType, $allowedFileTypes)) {
            if ($fileSize <= $maxFileSize) {
                // Generate a unique filename to prevent conflicts
                $fileName =  basename($_FILES["image"]["name"]);
                $targetFile = $uploadDirectory . $fileName;

                // Move the file to the target directory
                if (move_uploaded_file($uploadFile, $targetFile)) {
                    // File uploaded successfully
                    $newTutor = [
                        'name' => $_POST['name'],
                        'description' => $_POST['description'],
                        'image' => $fileName // Store the generated filename
                    ];
                    $tutors[] = $newTutor;
                    // Debugging statements
                    error_log("New tutor data before writing to file:");
                    error_log(print_r($newTutor, true));
                    writeTutorsToFile($tutorsFile, $tutors);
                    
                    // Debugging statement
                    error_log("Tutors array after writing to file:");
                    error_log(print_r($tutors, true));
                    // Set success message session variable
                    $_SESSION['successMessage'] = "Tutor added successfully.";
                    
                    // Redirect to prevent form resubmission
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    // Error uploading file
                    $_SESSION['errors'][] = "Sorry, there was an error uploading your file.";
                    $success = false;
                }
            } else {
                // Exceeded maximum file size limit
                $_SESSION['errors'][] = "File size exceeds the maximum limit of " . ($maxFileSize / (1024 * 1024)) . " MB.";
                $success = false;
            }
        } else {
            // Invalid file type
            $_SESSION['errors'][] = "Invalid file type. Please upload files in JPEG, PNG, or GIF format.";
            $success = false;
        }
    } elseif (isset($_FILES["image"]) && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE) {
        // Handle file upload errors
        $_SESSION['errors'][] = "File upload error: " . $_FILES["image"]["error"];
        $success = false;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Tutor</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <!-- Display success message if set -->
    <?php if(isset($successMessage) && !empty($successMessage)) {?>
        <div class="alert alert-success" role="alert">
            <?php echo $successMessage; ?>
        </div>
    <?php } ?>

    <h1 class="text-center mt-5">Add New Tutor</h1>


    <!-- Error and Success message placeholders -->
    <?php 
    if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {?>
        <div class="alert alert-danger" role="alert">
            <div class="error-heading"> <span>&#9888;</span>Error</div>
            <ul class="error-list">
                <?php foreach ($_SESSION['errors'] as $error) {
                    echo "<li>$error</li>";
                }?>
            </ul>
        </div>
    <?php 
    unset($_SESSION['errors']);
    }

    if(isset($_SESSION['successMessage']) && !empty($_SESSION['successMessage'])) {?>
        <div class="alert alert-success" role="alert">
            <?php echo $_SESSION['successMessage']; ?>
        </div>
    <?php 
    unset($_SESSION['successMessage']);
    } ?>



    <div class="row mt-3">
        <div class="col-md-6 offset-md-3">
            <form method="post" enctype="multipart/form-data" action="addtutorform.php">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image:</label>
                    <input type="file" class="form-control-file" id="image" name="image" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Tutor</button>
            </form>
        </div>
    </div>

    <!-- Display existing tutors in table format -->
    <div class="row mt-5">
        <div class="col-md-10 offset-md-1">
            <h2 class="text-center">Existing Tutors</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are tutors to display
                    if (!empty($tutors)) {
                        // Loop through each tutor and display their information in a table row
                        foreach ($tutors as $index => $tutor) {
                            ?>
                            <tr>
                                <td><?php echo $tutor['name']; ?></td>
                                <td><?php echo $tutor['description']; ?></td>
                                <td><img src="ourtutors/<?php echo $tutor['image']; ?>" alt="<?php echo $tutor['name']; ?>" style="max-width: 100px;"></td>
                                <td>
                                    <!-- Add a form to remove this tutor -->
                                    <form method="post">
                                        <input type="hidden" name="remove_tutor" value="<?php echo $index; ?>">
                                        <button type="submit" class="btn btn-danger">Remove Tutor</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        // Display a message if there are no tutors
                        ?>
                        <tr>
                            <td colspan="4" class="text-center">No tutors available</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
