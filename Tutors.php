<!DOCTYPE html>
<html lang="en">

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tutor">

    <title>Tutor</title>
    <!-- Assuming you have CSS for styling -->
    <link rel="stylesheet" href="styles.css">


    <?php
    include "inc/head.inc.php";
    include "inc/nav.inc.php";
    include 'tutors_functions.php';

    // Assuming tutors are stored in a JSON file named 'tutors.json'
    $tutorsFile = 'tutors.json';
    $tutors = readTutorsFromFile($tutorsFile);
    ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css?h=51a3dc46be9b9f91714755d28e0cc151">
    <link rel="stylesheet" href="../css/tutors.css">


</head>

    <body>
        <main>
            <section class="jumbotron text-center ">
                <div class="container">
                    <h1>Our Tutors</h1>
                    <p class="lead text-muted"><br>Thetuitioncenter is a leading educational institution dedicated to providing high-quality tutoring services to students of all ages and academic levels. With a team of experienced and qualified tutors, Thetuitioncenter offers comprehensive support in various subjects and exam preparation..<br></p>
                    <p><a class="btn btn-primary my-2" href="register.php">Register Now</a> <a class="btn btn-primary my-2" href="contactUs.php">Contact Us</a></p>
                </div>
            </section>
            <?php
            // Check if there are tutors to display
            if (!empty($tutors)) {
                ?>
                <div class="container mt-5">
                    <div class="row">
                        <?php
                        // Loop through each tutor and display their information
                        foreach ($tutors as $tutor) {
                            ?>
                            <div class="col-md-4">
                                <div class="card mb-4"> <!-- Added mb-4 class for bottom margin -->
                                <img src="/ourtutors/ourtutors/<?php echo $tutor['image']; ?>" class="card-img-top" alt="<?php echo $tutor['name']; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title">Tutor: <?php echo $tutor['name']; ?></h5>
                                        <p class="card-text"><?php echo $tutor['description']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            <?php
            } else {
                // Display a message if there are no tutors
                echo '<div class="container mt-5">No tutors available</div>';
            }
            ?>


    </main>



</body>

</html>
