<!DOCTYPE html>
<html lang="en">

<head>
    <title>About Us</title>
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/abouts/about-5/assets/css/about-5.css" />
    <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/abouts/about-1/assets/css/about-1.css" />
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
        <?php
    include "inc/head.inc.php";
    ?>
</head>

<body>

    <?php
    include "inc/nav.inc.php";
    ?>
    <section id="heroaboutus" class="d-flex justify-content-center align-items-center">
        <div class="container position-relative" data-aos="zoom-in" data-aos-delay="100">
        <h1>About Us</h1>
        </div>
    </section><!-- End Hero -->

    <main class="container">
        <!-- About 5 - Bootstrap Brain Component -->
        <section class="py-3 py-md-5 py-xl-8">
        <div class="container">
            <div class="row">
            <div class="col-12 col-md-10 col-lg-8">
                <h2 class="display-5 mb-4">Passionate teachers, Passionate Students</h2>
                <!-- <button type="button" class="btn btn-lg btn-primary mb-3 mb-md-4 mb-xl-5">Discover More</button> -->
            </div>
            </div>
        </div>

        <div class="container overflow-hidden">
            <div class="row gy-4 gy-lg-0">
            <div class="col-12 col-lg-6">
                <article>
                <div class="card border-0">
                    <img class="card-img-top img-fluid m-0" loading="lazy" src="./assets/img/about-img-1.jpg" alt="">
                    <div class="card-body border bg-white p-4">
                    <div class="entry-header mb-3">
                        <h2 class="card-title entry-title h4 mb-0">
                        <a class="link-dark text-decoration-none" href="#!">Our Vision</a>
                        </h2>
                    </div>
                    <p class="card-text entry-summary text-secondary mb-3">To be the leading education specialist across every subject and academic level </p>
                    </div>
                </div>
                </article>
            </div>
            <div class="col-12 col-lg-6">
                <article>
                <div class="card border-0">
                    <img class="card-img-top img-fluid m-0" loading="lazy" src="./assets/img/about-img-2.jpg" alt="">
                    <div class="card-body border bg-white p-4">
                    <div class="entry-header mb-3">
                        <h2 class="card-title entry-title h4 mb-0">
                        <a class="link-dark text-decoration-none" href="#!">Our Mission</a>
                        </h2>
                    </div>
                    <p class="card-text entry-summary text-secondary mb-3">Empowering students to achieve academic breakthroughs and excellence at every stage</p>
                    </div>
                </div>
                </article>
            </div>
            </div>
        </div>
        </section>
        <section id="about" class="about">
        <div class="container" data-aos="fade-up">

            <div class="row">
            <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-left" data-aos-delay="100">
                <img src="images/tuitionlesson.jpg" class="img-fluid" alt="">
            </div>
            <div class="col-lg-6 pt-4 pt-lg-0 order-2 order-lg-1 content">
                <h3>At The Tuition Centre, we care about where you want to go and how we can get you there.</h3>
                <p class="fst-italic">
                Our targeted approach to tuition empowers you to see and move beyond the challenges that so often get in the way of real academic growth. Encouraging you to learn, master, and finally feel confident—that’s when you know you’ve had a breakthrough, and after your first, it’s only a matter of time until you do it again.</p>
                <ul>
                <li><i class="bi bi-check-circle"></i> The way we teach inspires every lesson.</li>
                <li><i class="bi bi-check-circle"></i> Our study materials accelerate subject mastery.</li>
                <li><i class="bi bi-check-circle"></i> We go the extra mile with robust out-of-class support.</li>
                </ul>
                <p>
                Overall ensuring students are our main priority.
                </p>

            </div>
            </div>

        </div>
        </section>
        <section id="counts" class="counts section-bg">
        <div class="container">

            <div class="row counters">

            <div class="col-lg-3 col-6 text-center">
                <span data-purecounter-start="0" data-purecounter-end="<?php echo $_SESSION['numStudents']?>" data-purecounter-duration="1" class="purecounter"></span>
                <p>Students</p>
            </div>

            <div class="col-lg-3 col-6 text-center">
                <span data-purecounter-start="0" data-purecounter-end="64" data-purecounter-duration="1" class="purecounter"></span>
                <p>Courses</p>
            </div>

            <div class="col-lg-3 col-6 text-center">
                <span data-purecounter-start="0" data-purecounter-end="42" data-purecounter-duration="1" class="purecounter"></span>
                <p>Events</p>
            </div>

            <div class="col-lg-3 col-6 text-center">
                <span data-purecounter-start="0" data-purecounter-end="<?php echo $_SESSION['numTutors']?>" data-purecounter-duration="1" class="purecounter"></span>
                <p>Trainers</p>
            </div>

            </div>

        </div>
        </section>
        <section style="background-color: #F0F2F5;">
        <div class="container py-5">
            <div class="main-timeline">
                <h2>Milestones</h2>
            <div class="timeline left">
                <div class="card">
                <div class="card-body p-4">
                    <h3>2023</h3>
                    <p class="mb-0">The Tuition Centre's Parkway Parade branch opens, with more than 8,000 sq ft and over 10 classrooms</p>
                </div>
                </div>
            </div>
            <div class="timeline right">
                <div class="card">
                <div class="card-body p-4">
                    <h3>2021</h3>
                    <p class="mb-0">Branches at Bugis, Tampines opened up. </p>
                </div>
                </div>
            </div>
            <div class="timeline left">
                <div class="card">
                <div class="card-body p-4">
                    <h3>2020</h3>
                    <p class="mb-0">New branches at Royal Sqaure and Bukit Timah Shopping Centre opened. </p>
                </div>
                </div>
            </div>
            <div class="timeline right">
                <div class="card">
                <div class="card-body p-4">
                    <h3>2018</h3>
                    <p class="mb-0">Secondary Express programmes are established.</p>
                </div>
                </div>
            </div>
            <div class="timeline left">
                <div class="card">
                <div class="card-body p-4">
                    <h3>2017</h3>
                    <p class="mb-0">A second branch opens at Goldhill Plaza, and Secondary IP programmes are established.</p>
                </div>
                </div>
            </div>
            <div class="timeline right">
                <div class="card">
                <div class="card-body p-4">
                    <h3>2014</h3>
                    <p class="mb-0">The Tuition Centre is founded with a first branch at City Square Mall and the establishment of JC programmes.</p>
                </div>
                </div>
            </div>
            </div>
        </div>
        </section>
        <!-- About 1 - Bootstrap Brain Component -->
        <section class="py-3 py-md-5 py-xl-8">
        <div class="container">
            <div class="row gy-3 gy-md-4 gy-lg-0 align-items-lg-center">
            <div class="col-12 col-lg-6 col-xl-5">
                <img class="img-fluid rounded" id="teacher1" loading="lazy" src="images/groupofteachers.jpg" alt="">
            </div>
            <div class="col-12 col-lg-6 col-xl-7">
                <div class="row justify-content-xl-center">
                <div class="col-12 col-xl-11">
                    <h2 class="h1 mb-3">About Us</h2>
                    <p class="mb-5">The Tuition Centre is a premier education group specialising in Secondary Express, Secondary IP, and JC tuition. Our Indigo Pedagogy™, quality study materials, and robust out-of-class support complete the learning experience, inspiring breakthroughs at every academic stage.</p>
                    <div class="row gy-4 gy-md-0 gx-xxl-5X">
                    <div class="col-12 col-md-6">
                        <div class="d-flex">
                        <div class="me-4 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                            <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16Zm0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15Z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="mb-3">Achieve your breakthrough</h4>
                            <p class="text-secondary mb-0"> We want the best for you. We're here to see your ambitions through with you. </p>
                        </div>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
        </section>
        <!--testimonials section-->
        <section id="testimonials" class="testimonials">
        <div class="container" data-aos="fade-up">

            <div class="section-title">
            <h2>Testimonials</h2>
            <p>What are they saying</p>
            </div>
            <div class="testimonials-slider swiper" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper-wrapper">

                <div class="swiper-slide">
                <div class="testimonial-wrap">
                    <div class="testimonial-item">
                    <img src="images/student1.jpg" class="testimonial-img" alt="">
                    <h3>Vanessa Lim</h3>
                    <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        After taking Ms Janice's classes, my grades have improved so much, scoring mostly As for Mathematics. Her classes are interactive and easy to understand. 
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                    </p>
                    </div>
                </div>
                </div><!-- End testimonial item -->

                <div class="swiper-slide">
                <div class="testimonial-wrap">
                    <div class="testimonial-item">
                    <img src="images/student2.jpg" class="testimonial-img" alt="">
                    <h3>Alfred Sim</h3>
                    <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        After taking Ms Janice's classes, my grades have improved so much, scoring mostly As for Mathematics. Her classes are interactive and easy to understand. 
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                    </p>
                    </div>
                </div>
                </div><!-- End testimonial item -->

                <div class="swiper-slide">
                <div class="testimonial-wrap">
                    <div class="testimonial-item">
                    <img src="images/student3.jpg" class="testimonial-img" alt="">
                    <h3>Wilbert Toh</h3>
                    <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        After taking Ms Janice's classes, my grades have improved so much, scoring mostly As for Mathematics. Her classes are interactive and easy to understand. 
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                    </p>
                    </div>
                </div>
                </div><!-- End testimonial item -->

                <div class="swiper-slide">
                <div class="testimonial-wrap">
                    <div class="testimonial-item">
                    <img src="images/student4.jpg" class="testimonial-img" alt="">
                    <h3>Valerie Lim</h3>
                    <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        After taking Ms Janice's classes, my grades have improved so much, scoring mostly As for Mathematics. Her classes are interactive and easy to understand. 
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                    </p>
                    </div>
                </div>
                </div><!-- End testimonial item -->

                <div class="swiper-slide">
                <div class="testimonial-wrap">
                    <div class="testimonial-item">
                    <img src="images/student5.jpg" class="testimonial-img" alt="">
                    <h3>Amanda Khoo</h3>
                    <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        After taking Ms Janice's classes, my grades have improved so much, scoring mostly As for Mathematics. Her classes are interactive and easy to understand. 
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                    </p>
                    </div>
                </div>
                </div><!-- End testimonial item -->

            </div>
            <div class="swiper-pagination"></div>
            </div>

        </div>
        </section><!-- End testimonial section -->
        <?php
        include "inc/footer.inc.php";
        ?>
    </main>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="js/purecounter.js"></script>
</body>

</html>