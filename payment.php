<?php
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// If selected classes got nothing, bring the user to error page
if (!isset($_POST['selectedClasses'])) {
    header('Location: error.php');
    exit();
}
?>
<head>
    <title>Payment Page</title>
    <?php include "inc/head.inc.php"; ?>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>
    <main class="container">
        <div class="row mt-3 mb-3">
            <h1 class="col">Payment Page</h1>
        </div>
        <div class="row selected-classes">
            <div class="col">
                <h2>Your Selected Classes</h2>
                <ul>
                    <?php
                        $selectedClasses = json_decode($_POST['selectedClasses'], true);
                        date_default_timezone_set('Asia/Singapore');

                        foreach ($selectedClasses as $class) {
                            $startDateTime = new DateTime($class['startTime']);
                            $endDateTime = new DateTime($class['endTime']);
                            $formattedStartTime = $startDateTime->format('Y-m-d g:i A');
                            $formattedEndTime = $endDateTime->format('g:i A');
                        
                            echo '<li>' . htmlspecialchars($class['title']) . ' on ' . $formattedStartTime . ' to ' . $formattedEndTime . '</li>';
                        }
                        
                        
                    ?>
                </ul>
            </div>
            <div class="col">
                <h3>Total Amount Payable:</h3>
                <?php
                    $totalAmount = 0.00;
                    foreach ($selectedClasses as $class) {
                        $totalAmount += $class['price'];
                    }
                    echo '<span>$' . number_format($totalAmount, 2) . '</span>';
                ?>
            </div>
        </div>

        <form method="post" action="payment/process_payment.php" class="mt-4">
            <div class="mb-3">
                <label for="cardNumber" class="form-label">Card Number</label>
                <input type="text" class="form-control" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456" required>
            </div>
            <div class="mb-3">
                <label for="cardHolderName" class="form-label">Cardholder Name</label>
                <input type="text" class="form-control" id="cardHolderName" name="cardHolderName" placeholder="Name of Cardholder" required>
            </div>
            <div class="mb-3">
                <label for="expiryDate" class="form-label">Expiry Date</label>
                <input type="month" class="form-control" id="expiryDate" name="expiryDate" required>
            </div>
            <div class="mb-3">
                <label for="cvc" class="form-label">CVC</label>
                <input type="text" class="form-control" id="cvc" name="cvc" placeholder="CVC" required>
            </div>
            <input type="hidden" name="selectedClasses" value='<?php echo htmlspecialchars(json_encode($selectedClasses)); ?>'>
            <input type="hidden" name="totalAmount" value='<?php echo htmlspecialchars($totalAmount); ?>'>
            <button type="submit" class="btn btn-primary mb-3">Pay Now</button>
        </form>
    </main>
    <?php include "inc/footer.inc.php"; ?>
</body>
