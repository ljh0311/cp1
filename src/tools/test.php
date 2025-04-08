<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Basic PHP info
echo "<h1>PHP Test Page</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

// Test SQLite
echo "<h2>SQLite Test</h2>";
if (extension_loaded('sqlite3')) {
    echo "<p style='color: green;'>✓ SQLite3 extension is loaded</p>";
    try {
        $sqlite = new SQLite3(':memory:');
        echo "<p style='color: green;'>✓ SQLite3 connection successful</p>";
        $sqlite->close();
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ SQLite3 error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ SQLite3 extension is not loaded</p>";
}

// Test PDO
echo "<h2>PDO Test</h2>";
if (extension_loaded('pdo')) {
    echo "<p style='color: green;'>✓ PDO extension is loaded</p>";
    echo "<p>Available PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "</p>";
} else {
    echo "<p style='color: red;'>✗ PDO extension is not loaded</p>";
}

// Test session
echo "<h2>Session Test</h2>";
if (session_start()) {
    echo "<p style='color: green;'>✓ Session started successfully</p>";
    echo "<p>Session save path: " . session_save_path() . "</p>";
} else {
    echo "<p style='color: red;'>✗ Failed to start session</p>";
}

// Directory permissions
echo "<h2>Directory Permissions</h2>";
$dirs = [
    '.' => 'Current directory',
    './sessions' => 'Sessions directory',
    './logs' => 'Logs directory',
    './uploads' => 'Uploads directory'
];

foreach ($dirs as $dir => $name) {
    if (file_exists($dir)) {
        echo "<p>" . htmlspecialchars($name) . ": ";
        echo is_writable($dir) ? 
            "<span style='color: green;'>✓ Writable</span>" : 
            "<span style='color: red;'>✗ Not writable</span>";
        echo "</p>";
    } else {
        echo "<p>" . htmlspecialchars($name) . ": <span style='color: red;'>✗ Does not exist</span></p>";
    }
}

// PHP extensions
echo "<h2>Required Extensions</h2>";
$required_extensions = [
    'pdo',
    'pdo_mysql',
    'pdo_sqlite',
    'sqlite3',
    'mysqli',
    'mbstring',
    'json',
    'curl',
    'gd',
    'xml'
];

foreach ($required_extensions as $ext) {
    echo "<p>$ext: ";
    echo extension_loaded($ext) ? 
        "<span style='color: green;'>✓ Loaded</span>" : 
        "<span style='color: red;'>✗ Not loaded</span>";
    echo "</p>";
}

// Environment variables
echo "<h2>Environment</h2>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>PHP SAPI: " . php_sapi_name() . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
?> 