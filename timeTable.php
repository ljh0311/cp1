<?php
// Ensure the session has started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<head>
    <title>Weekly Timetable</title>
    <?php
    include 'inc/head.inc.php';
    ?>
    <link rel="stylesheet" href="../css/timetable.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js" integrity="sha512-LsnSViqQyaXpD4mBBdRYeP6sRwJiJveh2ZIbW41EBrNmKxgr/LFZIiWT6yr+nycvhvauz8c2nYMhrP80YhG7Cw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

</head>

<body>
    <?php
    include "inc/nav.inc.php";
    ?>
    <div class="container">
        <?php
        echo $_SESSION['userTypeId'] == '2' ?
            '<h1 class="text-center mt-4">Student Schedule</h1>'
            : ' <h1 class="text-center mt-4">Tutor Schedule</h1>';
        ?>
       
        <div class="row">
            <div class="col md-12">
                <div class="float-right">
                    <button type="button" id="editButton" class="btn btn-primary edit-button" disabled>
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>

                    <?php
                    echo $_SESSION['userTypeId'] == '3' ?
                        ' <button type="button" id="deleteButton" class="btn btn-danger delete-class" disabled>
                        <i class="fa-solid fa-trash"></i>
                    </button>'
                        : '';
                    ?>

                    <?php
                    echo $_SESSION['userTypeId'] == '3' ?
                        '<button type="button" id="createButton" class="btn btn-primary create-button">
            <i class="fa-solid fa-plus"></i>
        </button>'
                        : '';
                    ?>
                </div>
            </div>

        </div>

        <?php

        if ($_SESSION['userTypeId'] === 3) {
        ?>

            <div id="timetable-container" class="mt-4">
                <!-- Tutor timetable list will be generated here -->
            </div>

        <?php
        } else if ($_SESSION['userTypeId'] === 2) {

        ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-fixed">
                    <thead>
                        <tr>
                            <!-- table headers with dates will be generated here -->
                        </tr>
                    </thead>
                    <tbody id="timetable-body">
                        <!-- time slots will be generated here -->
                    </tbody>
                </table>
            </div>

            <div class="navigation-buttons text-center mt-4">
                <button id="prevWeekButton" class="btn btn-primary">Previous Week</button>
                <button id="nextWeekButton" class="btn btn-primary">Next Week</button>
            </div>
        <?php
        }
        ?>
    </div>
    <?php
    include 'timetable/edit_modal.php';
    include 'timetable/create_modal.php';
    include 'timetable/delete_modal.php';
    include "inc/footer.inc.php";

    ?>
</body>
<script>
    var userID = <?php echo json_encode($userID); ?>;
    var userTypeID = <?php echo json_encode($userTypeID); ?>;
</script>
<script defer src="js/timetable.js"></script>
<script defer src="js/generateTable2.js"></script>