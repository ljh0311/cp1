<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top" data-bs-theme="bright">
    <style>
        body {
            padding-top: 120px;

        }
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            /* Ensure the navbar appears on top of other content */
        }
    </style>
    <div class="container-fluid">
        <img class="rounded-circle" src="../images/logo.png" alt="Logo" width="160" height="90">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation" style="padding-right: 10px;">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php
                // Ensure the session has started
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                // Check if the user is logged in using session, not cookies
                if (isset($_SESSION['userID'])) {

                    echo '<li class="nav-item"><span class="nav-link">Welcome, ' . htmlspecialchars($_SESSION['fname']) . '</span></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="/index.php">Home</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>';

                    $userTypeId = $_SESSION['userTypeId'];

                    switch ($userTypeId) {
                        case 1:
                            echo '<li class="nav-item"><a class="nav-link" href="/dashboard/dashboard.php">Admin Dashboard</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="/registerTutor.php">Register Tutor</a></li>';
                            break;
                        case 2:
                            echo '<li class="nav-item"><a class="nav-link" href="/timeTable.php">Timetable</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="/booking.php">Book Classes</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="/profile.php">Profile</a></li>';
                            break;
                        case 3:
                            echo '<li class="nav-item"><a class="nav-link" href="/timeTable.php">Timetable</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="/profileTutor.php">Profile</a></li>';
                            break;
                    }
                } else {
                    // Display default menu items for guests if user is not logged in
                    echo '<li class="nav-item"><span class="nav-link">Welcome guest! </span></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="/index.php">Home</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="/aboutUs.php">About Us</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="/Tutors.php">Our Tutors</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="/contactUs.php">Contact Us</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="/register.php">Register</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>