<?php
// Function to read tutor data from file
function readTutorsFromFile($filename) {
    $tutorsData = file_get_contents($filename);

    // Debugging statement
    if ($tutorsData !== false) {
        error_log("Tutors data successfully read from file: $filename");
    } else {
        error_log("Error reading tutors data from file: $filename");
    }

    return json_decode($tutorsData, true);
}


// Function to write tutor data to file
function writeTutorsToFile($filename, $tutors) {
    $tutorsData = json_encode($tutors, JSON_PRETTY_PRINT);
    $result = file_put_contents($filename, $tutorsData);

    // Debugging statements
    if ($result !== false) {
        error_log("Tutors data successfully written to file: $filename");
    } else {
        error_log("Error writing tutors data to file: $filename");
    }

    return $result;
}


?>