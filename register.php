<?php
session_start();

// Generate CSRF token and store it in session
function generateCSRFToken() {
    return bin2hex(random_bytes(32));
}

// Regenerate CSRF token if it's missing or invalid
if (!isset($_SESSION['csrf_token']) || !validateCSRFToken($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateCSRFToken();
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Validate CSRF token on form submission
function protectAgainstCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check CSRF token
        $submittedToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
        if (!validateCSRFToken($submittedToken)) {
            // Invalid CSRF token, handle error
            die("CSRF token validation failed!");
        }
    }
}

// Additional security measure: Regenerate CSRF token on each request
$_SESSION['csrf_token'] = generateCSRFToken();

// Call the CSRF protection function
protectAgainstCSRF();

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register an account now!</title>
    <?php
    include "inc/head.inc.php";
    include "inc/nav.inc.php";
    ?>
    <link rel="stylesheet" href="../css/register.css">
<style>
body{
    background:url('https://mdbcdn.b-cdn.net/img/Photos/new-templates/search-box/img4.webp') no-repeat center center fixed;
    -webkit-background-size:cover;
    -moz-background-size:cover;
    -o-background-size: cover;
    background-size: cover;
}
</style>


    
</head>

<body>
<main>
    <section class="vh-100 bg-image">
        <div class="mask d-flex align-items-center h-100 gradient-custom-3">
            <div class="container h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-9 col-lg-7 col-xl-6">
                        <div class="card" style="border-radius: 15px;">
                            <div class="card-body p-5">
                            <h2 class="text-uppercase text-center mb-5">Create an account</h2>
                            <?php
                        /* This PHP code block is checking if the variable `` is not empty. If `` is not empty, it will display an alert message with a list of errors. */

                        if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
                            $errors = $_SESSION['errors'];
                            echo '<div class="alert alert-danger" role="alert">';
                            echo '<div class="error-heading"> <span>&#9888;</span>Error</div>';
                            echo '<ul class="error-list">';
                            
                            foreach ($errors as $error) {
                                if (is_array($error)) {
                                    foreach ($error as $singleError) {
                                        echo "<li>$singleError</li>";
                                    }
                                } else {
                                    echo "<li>$error</li>";
                                }
                            }
                            
                            echo '</ul>';
                            echo '</div>';
                            // Clear errors from session
                            unset($_SESSION['errors']);
                        }
                        
                        ?>
                        
                            <form action="user/process_register.php" method="post">
                              <!-- CSRF token field -->
                              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-outline mb-4">
                                        <input maxlength="45" required type="text" id="fname" name="fname" class="form-control form-control-lg" placeholder="First Name" />
                                        <label class="form-label" for="fname"></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-outline mb-4">
                                        <input maxlength="45" required type="text" id="lname" name="lname" class="form-control form-control-lg" placeholder="Last Name" />
                                        <label class="form-label" for="lname"></label>
                                    </div>
                                </div>
                            </div>

                                <div class="form-outline mb-4">
                                    <input maxlength="45" required type="email" id="email" name="email" class="form-control form-control-lg" placeholder="Email Address" />
                                    <label class="form-label" for="email"></label>
                                </div>

                                <div class="form-outline mb-4">
                                    <input maxlength="45" required type="text" id="username" name="username" class="form-control form-control-lg" placeholder="Username" />
                                    <label class="form-label" for="username"></label>
                                </div>

                                <div class="form-outline mb-4">
                                    <input required type="password" id="pwd" name="pwd" class="form-control form-control-lg" placeholder="Password" />
                                    <label class="form-label" for="pwd"></label>
                                </div>

                                <div class="form-outline mb-4">
                                    <input required type="password" id="pwd_confirm" name="pwd_confirm" class="form-control form-control-lg" placeholder="Confirm password"/>
                                    <label class="form-label" for="pwd_confirm"></label>
                                </div>
                    
                                <div class="form-check d-flex justify-content-center mb-5">
                                    <input class="form-check-input me-2" required type="checkbox" value="" id="agree" />
                                    <label class="form-check-label" for="agree">
                                        I agree all statements in <a href="termsconditon.php" class="text-body"><u>Terms of service</u></a>
                                    </label>
                                </div>

                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="btn btn-success btn-block btn-lg gradient-custom-4 text-body">Register</button>
                                </div>

                                <p class="text-center text-muted mt-5 mb-0">Have already an account? <a href="login.php"
                                    class="fw-bold text-body"><u>Login here</u></a></p>

                            </form>

                        </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</section>
</main>
</body>