<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <?php
    session_start();

    // Check if the user is logged in and has admin role
    if (isset($_SESSION['admin']) && $_SESSION['admin'] === true && $_SESSION['role'] === 'admin') {
        // Display the user creation form
        echo "<h1>Admin Dashboard</h1>";
        echo "<form action='process_createAdmin.php' method='post'>";
        echo "<input type='text' name='email' required placeholder='Email'>";
        echo "<input type='text' name='username' required placeholder='Username'>";
        echo "<input type='password' name='password' required placeholder='Password'>";
        echo "<select name='userTypeID' required>";
        echo "<option value='1'>Admin</option>";
        echo "<option value='2'>Student</option>";
        echo "<option value='3'>Tutor</option>";
        echo "</select>";
        echo "<button type='submit' class='btn btn-primary'>Create</button>";
        echo "</form>";
    } else {
        // Redirect to unauthorized access page or login page
        header('Location: unauthorized.php');
        exit;
    }
    ?>
</body>
</html>
