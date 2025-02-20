<?php
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}
?>
<head>
    <title>Booking Page</title>
    <?php
      include "inc/head.inc.php";
    ?>
    <script defer src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script defer src="js/booking.js"></script>
</head>
<body>
    <?php
    include "inc/nav.inc.php";
    ?>
    <main class="container">
        <div class="row mt-3 mb-3">
            <h1 class="col">Book a Class</h1>
            <button type="submit" class="col btn btn-primary" id="submitBtn" disabled>Proceed to payment</button>
        </div>
        <div id="calendar"></div>
    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>
