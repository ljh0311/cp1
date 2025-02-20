<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/abouts/about-5/assets/css/about-5.css" />
  <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/abouts/about-1/assets/css/about-1.css" />
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <?php
  include "inc/head.inc.php";
  ?>
  <title>The Tuition Centre</title>

</head>

<body>

  <?php
  include "inc/nav.inc.php";
  include "inc/header.inc.php";
  ?>

  <main class="container">
    <section id="hero" class="d-flex justify-content-center align-items-center" style="background-image: url('images/languageclass.jpg'); background-size: cover; background-position: center;">
      <div class="container position-relative" data-aos="zoom-in" data-aos-delay="100">
        <h1>Learning Today,<br>Leading Tomorrow</h1>
        <h2>Let's get started, shall we?</h2>
        <a href="booking.php" class="btn-get-started">Let's get tutoring!</a>
      </div>
    </section><!-- End Hero -->

    <section id="why-us" class="why-us">
      <div class="container" data-aos="fade-up">
        <div class="row">
          <div class="col-lg-4 d-flex align-items-stretch">
            <div class="content">
              <h3>Why Choose Our Tuition Center?</h3>
              <p>
                Being just a decade old, we have dedicated ourselves in providing excellent tutor and consultation services to student. Providing personalized support and guidance, we aim to help students form a stable foundation in their academic path.
              </p>
              <div class="text-center">
                <a href="aboutUs.php" class="more-btn">Learn More <i class="bx bx-chevron-right"></i></a>
              </div>
            </div>
          </div>
          <div class="col-lg-8 d-flex align-items-stretch" data-aos="zoom-in" data-aos-delay="100">
            <div class="icon-boxes d-flex flex-column justify-content-center">
              <div class="row">
                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box mt-4 mt-xl-0">
                    <i class="bx bx-receipt"></i>
                    <h4>Expert English Consultations</h4>
                    <p>Our English consultations focus on enhancing language skills, improving writing proficiency, and fostering critical thinking abilities.</p>
                  </div>
                </div>
                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box mt-4 mt-xl-0">
                    <i class="bx bx-cube-alt"></i>
                    <h4>Maths Mastery Sessions</h4>
                    <p>Join our Maths mastery sessions to strengthen your problem-solving skills, grasp fundamental concepts, and excel in mathematical reasoning.</p>
                  </div>
                </div>
                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box mt-4 mt-xl-0">
                    <i class="bx bx-images"></i>
                    <h4>Personalized Learning Approach</h4>
                    <p>Benefit from our personalized learning approach tailored to meet the unique needs and learning styles of each student.</p>
                  </div>
                </div>
              </div>
            </div><!-- End .content-->
          </div>
        </div>
      </div>
    </section>

    <!-- Add spacing here -->
    <div style="height: 50px;"></div>

    <section id="trainers" class="trainers">
      <h2>Our tutors</h2>
      <div id="tutorsCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="container" data-aos="fade-up">
              <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
                <div class="col col-md-6 d-flex align-items-stretch">
                  <div class="member">
                    <h3>English Tutor</h3>
                    <figure>
                      <img src="images\student5.jpg" class="img-fluid" width='300px' alt="English Tutor" title="View larger image..." />
                      <figcaption>Meet our English Tutor</figcaption>
                    </figure>
                    <p>
                      Our experienced English tutor offers personalized 1-to-1 consultations to help
                      students excel in language skills.
                      <br> We provide the following services for students:
                    </p>
                  </div>
                </div>
                <div class="col-md-6 d-flex align-items-stretch">
                  <div class="tutormember">
                    <h3>John</h3>
                    <p>
                      Specialisation
                    <ul>
                      <li>Oral/Presentation practice</li>
                      <li>Listening comprehension</li>
                      <li>Essay writing</li>
                      <li>Letter writing</li>
                    </ul>
                    <h3>Kent</h3>
                    <p>
                      Specialisation
                    <ul>
                      <li>Persuation writing</li>
                      <li>Litreture</li>
                    </ul>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="container" data-aos="fade-up">
              <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
                <div class="col-lg-4 col-md-6 d-flex align-items-stretch">
                  <div class="member">
                    <h3>Maths Tutor</h3>
                    <figure>
                      <img src="images\student4.jpg" class="img-fluid" width='300px' alt="Maths Tutor" title="View larger image..." />
                      <figcaption>Meet our Maths Tutor</figcaption>
                    </figure>
                    <p>
                      Our dedicated Maths tutor provides tailored support in solving mathematical
                      problems, ensuring students grasp concepts effectively.
                    </p>
                  </div>
                </div>
                <div class="col-md-6 d-flex align-items-stretch">
                  <div class="tutormember">
                    <h3>Kelly</h3>
                    <p>
                      Specialisation
                    <ul>
                      <li>Algebra</li>
                      <li>Differentiation and integration</li>
                      <li>Probability and statistics</li>
                    </ul>
                    <h3>Janessa</h3>
                    <p>
                      Specialisation
                    <ul>
                      <li>Modulus</li>
                      <li>Sets and notation</li>
                      <li>Calculus</li>
                    </ul>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="container" data-aos="fade-up">
              <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
                <div class="col-lg-4 col-md-6 d-flex align-items-stretch">
                  <div class="member">
                    <h3>Science Tutor</h3>
                    <figure>
                      <img src="images\student1.jpg" class="img-fluid" width='300px' alt="Science Tutor" title="View larger image..." />
                      <figcaption>Meet our Science Tutor</figcaption>
                    </figure>
                    <p>
                      Our dedicated Science tutor offers personalized 1-to-1 consultations to help
                      students excel in understanding scientific concepts and experiments.
                    </p>
                  </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex align-items-stretch">
                  <div class="tutormember">
                    <h3>Jazz</h3>
                    <p>
                      Tutor
                    <ul>
                      <li>Physics</li>
                      <li>Biology</li>
                      <li>Chemistry</li>
                    </ul>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#tutorsCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#tutorsCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
    </section>
    <div class="col-lg-12 text-center mt-4">
      <a href="Tutors.php" class="btn btn-primary">Meet the rest of the team</a>
    </div>

    <div style="height: 50px;"></div>

    <?php
    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
      // Display an error message if database config file cannot be read
      echo "Failed to read database config file.";
      exit;
    }

    $conn = new mysqli(
      $config['servername'],
      $config['username'],
      $config['password'],
      $config['dbname']
    );

    // Check connection
    if ($conn->connect_error) {
      // Display an error message if database connection fails
      echo "Connection failed: " . $conn->connect_error;
      exit;
    }

    // Query to count the number of rows in the students table
    $sql_students = "SELECT COUNT(*) AS num_students FROM thetuitioncenter.students";
    $result_students = $conn->query($sql_students);
    $row_students = $result_students->fetch_assoc();
    $numStudents = $row_students['num_students'];
    $_SESSION['numStudents'] = $row_students['num_students'];


    // Query to count the number of rows in the tutors table
    $sql_tutors = "SELECT COUNT(*) AS num_tutors FROM thetuitioncenter.tutors";
    $result_tutors = $conn->query($sql_tutors);
    $row_tutors = $result_tutors->fetch_assoc();
    $numTutors = $row_tutors['num_tutors'];
    $_SESSION['numTutors'] = $row_tutors['num_tutors'];
    ?>

    <section id="forum">
      <div class="container" data-aos="fade-up">
        <div class="row" data-aos="zoom-in" data-aos-delay="100">
          <div class="col-sm">
            <h2>Student Forum</h2>
            <article>
              <h3>Ask Questions</h3>
              <div class="row">
                <div class="col-md-6">
                  <figure>
                    <img class="img-fluid" src="images\teacherwstudent.jpg" alt="Student Forum" title="View larger image..." />
                    <figcaption>Engage with the Student Forum</figcaption>
                  </figure>
                </div>
                <div class="col-md-6">
                  Our online student forum provides a platform for students to post questions and seek assistance from tutors and peers.
                  <div class="btn-container">
                    <a href="aboutUs.php#testimonials" class="btn btn-primary">Read testimonials from our satisfied users!</a>
                    <a href="contactUs.php" class="btn btn-secondary">Contact Us</a>
                  </div>
                  <section id="counts" class="counts section-bg">
                    <div class="container">
                      <div class="row counters">
                        <h2> Our member size: </h2>
                        <div class="col-lg-3 col-6 text-center">
                          <span data-purecounter-start="0" data-purecounter-end="<?php echo $_SESSION['numStudents'] ?>" data-purecounter-duration="1" class="purecounter"></span>
                          <p>Students</p>
                        </div>
                        <div class="col-lg-3 col-6 text-center">
                          <span data-purecounter-start="0" data-purecounter-end="<?php echo $_SESSION['numTutors'] ?>" data-purecounter-duration="1" class="purecounter"></span>
                          <p>Trainers</p>
                        </div>
                      </div>
                    </div>
                  </section>
                </div>
              </div>
          </div>
          </article>
        </div>
      </div>
      </div>
    </section>
    <?php
    include "inc/footer.inc.php";
    ?>
  </main>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="js/purecounter.js"></script>
</body>

</html>