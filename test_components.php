<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Component Test Results:</h2>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test:</h3>";
try {
    require_once 'database/dbConn.php';
    $conn = getDbConnection();
    echo "<span style='color: green;'>✓ Database connection successful!</span>";
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</span>";
}

// Test 2: Required Files
echo "<h3>2. Required Files Test:</h3>";
$required_files = [
    'inc/head.inc.php',
    'inc/nav.inc.php',
    'inc/footer.inc.php',
    'inc/ErrorHandler.php',
    'css/main.css'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<span style='color: green;'>✓ {$file} exists</span><br>";
    } else {
        echo "<span style='color: red;'>✗ {$file} is missing</span><br>";
    }
}

// Test 3: Session
echo "<h3>3. Session Test:</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['test'] = 'test_value';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'test_value') {
    echo "<span style='color: green;'>✓ Session is working</span>";
} else {
    echo "<span style='color: red;'>✗ Session is not working</span>";
}

// Test 4: PHP Configuration
echo "<h3>4. PHP Configuration:</h3>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";
echo "Error Reporting Level: " . error_reporting() . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
?> 