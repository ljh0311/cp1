<head>
    <title>Success Page</title>
    <?php
      include "inc/head.inc.php";
    ?>
    <?php
    $successMessage = isset($_GET['message']) ? $_GET['message'] : 'Your request was successful!';
    ?>
</head>
<body>
    <?php
    include "inc/nav.inc.php";
    ?>
    <main class="container">
        <div class="container mt-5 mb-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">Success!</h4>
                        <p><?php echo htmlspecialchars($successMessage); ?></p>
                        <hr>
                    </div>
                    <a href="index.php" class="btn btn-primary">Go to Home</a>
                </div>
            </div>
        </div>
    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>
