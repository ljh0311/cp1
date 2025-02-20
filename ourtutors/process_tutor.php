<?php
include 'tutors_functions.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Define the upload directory
    $uploadDirectory = "ourtutors/";

    // Check if the directory exists, if not, create it
    if (!file_exists($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true); // Create the directory with full permissions
    }

    // Path to store the uploaded image
    $targetFile = $uploadDirectory . basename($_FILES["image"]["name"]);

    // Move uploaded image to the 'uploads' directory
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        // File uploaded successfully
        // Retrieve other form data
        $name = $_POST['name'];
        $description = $_POST['description'];
        $imageName = $_FILES['image']['name'];

        // Add the new tutor to the array
        $newTutor = [
            'name' => $name,
            'description' => $description,
            'image' => $imageName
        ];

        // Read existing tutors from file
        $tutorsFile = 'tutors.json';
        $tutors = readTutorsFromFile($tutorsFile);

        // Add the new tutor to the array of tutors
        $tutors[] = $newTutor;

        // Write updated list of tutors to file
        writeTutorsToFile($tutorsFile, $tutors);

        // Redirect back to the form page with a success message
        header("Location: Tutors.php?status=success");
        exit();
    } else {
        // Error uploading file
        echo "Sorry, there was an error uploading your file.<br>";
    }
} else {
    // Redirect back to the form page if accessed directly
    header("Location: Tutors.php");
    exit();
}
?>
