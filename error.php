<head>
    <title>Error Page</title>
    <?php
      include "inc/head.inc.php";
    ?>
    <?php
    $errorMessage = isset($_GET['message']) ? $_GET['message'] : 'An unknown error occurred.';
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
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Error Occurred!</h4>
                        <p><?php echo htmlspecialchars($errorMessage); ?></p>
                        <hr>
                        <p class="mb-0">Please try one of the options below to proceed.</p>
                    </div>
                    <a href="index.php" class="btn btn-primary">Go to Home</a>
                    <button onclick="history.back()" class="btn btn-secondary">Go Back</button>
                </div>
            </div>
        </div>
    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>
