<?php
// Enable error reporting for diagnostics
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path
define('ROOT_PATH', __DIR__);

// Function to check directory permissions
function checkDirectoryPermissions($dir) {
    $result = [
        'exists' => false,
        'writable' => false,
        'permissions' => '',
        'owner' => '',
        'group' => ''
    ];
    
    if (file_exists($dir)) {
        $result['exists'] = true;
        $result['writable'] = is_writable($dir);
        $result['permissions'] = substr(sprintf('%o', fileperms($dir)), -4);
        $result['owner'] = posix_getpwuid(fileowner($dir))['name'];
        $result['group'] = posix_getgrgid(filegroup($dir))['name'];
    }
    
    return $result;
}

// Function to check PHP extensions
function checkPhpExtensions() {
    $required_extensions = [
        'pdo',
        'pdo_mysql',
        'sqlite3',
        'mysqli',
        'mbstring',
        'json',
        'curl',
        'gd',
        'xml'
    ];
    
    $results = [];
    foreach ($required_extensions as $ext) {
        $results[$ext] = extension_loaded($ext);
    }
    return $results;
}

// Function to check MySQL connection
function checkMySQLConnection() {
    try {
        require_once 'database/dbConn.php';
        $conn = getDbConnection();
        return [
            'success' => true,
            'message' => 'MySQL connection successful'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'MySQL connection failed: ' . $e->getMessage()
        ];
    }
}

// Function to check SQLite
function checkSQLite() {
    try {
        $dbPath = __DIR__ . '/database/fallback.db';
        $sqlite = new SQLite3($dbPath);
        return [
            'success' => true,
            'message' => 'SQLite connection successful',
            'path' => $dbPath,
            'exists' => file_exists($dbPath)
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'SQLite error: ' . $e->getMessage()
        ];
    }
}

// Function to check required files
function checkRequiredFiles() {
    $required_files = [
        'index.php',
        'books.php',
        'admin/admin_dashboard.php',
        'admin/process_book.php',
        'inc/autoload.php',
        'inc/config.php',
        'inc/ErrorHandler.php',
        'inc/footer.inc.php',
        'inc/head.inc.php',
        'inc/nav.inc.php',
        'inc/session_start.php',
        'database/DatabaseManager.php',
        'database/dbConn.php',
        'css/main.css',
        'js/main.js'
    ];
    
    $results = [];
    foreach ($required_files as $file) {
        $path = ROOT_PATH . '/' . $file;
        $results[$file] = [
            'exists' => file_exists($path),
            'readable' => is_readable($path),
            'size' => file_exists($path) ? filesize($path) : 0
        ];
    }
    return $results;
}

// Perform all checks
$checks = [
    'system' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
        'current_dir' => __DIR__,
        'free_disk_space' => disk_free_space('/'),
        'total_disk_space' => disk_total_space('/')
    ],
    'directories' => [
        'uploads' => checkDirectoryPermissions(ROOT_PATH . '/uploads'),
        'logs' => checkDirectoryPermissions(ROOT_PATH . '/logs'),
        'sessions' => checkDirectoryPermissions(ROOT_PATH . '/sessions'),
        'database' => checkDirectoryPermissions(ROOT_PATH . '/database')
    ],
    'php_extensions' => checkPhpExtensions(),
    'mysql' => checkMySQLConnection(),
    'sqlite' => checkSQLite(),
    'required_files' => checkRequiredFiles()
];

// Output results as HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>System Diagnostic - <?php echo $_SERVER['SERVER_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .check-status {
            padding: 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
        .check-success { background-color: #d1e7dd; }
        .check-error { background-color: #f8d7da; }
        .check-warning { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">System Diagnostic Report</h1>
        
        <!-- System Information -->
        <section class="mb-5">
            <h2>System Information</h2>
            <div class="card">
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">PHP Version</dt>
                        <dd class="col-sm-9"><?php echo $checks['system']['php_version']; ?></dd>
                        
                        <dt class="col-sm-3">Server Software</dt>
                        <dd class="col-sm-9"><?php echo $checks['system']['server_software']; ?></dd>
                        
                        <dt class="col-sm-3">Document Root</dt>
                        <dd class="col-sm-9"><?php echo $checks['system']['document_root']; ?></dd>
                        
                        <dt class="col-sm-3">Current Directory</dt>
                        <dd class="col-sm-9"><?php echo $checks['system']['current_dir']; ?></dd>
                        
                        <dt class="col-sm-3">Free Disk Space</dt>
                        <dd class="col-sm-9"><?php echo number_format($checks['system']['free_disk_space'] / 1024 / 1024, 2); ?> MB</dd>
                    </dl>
                </div>
            </div>
        </section>

        <!-- Directory Permissions -->
        <section class="mb-5">
            <h2>Directory Permissions</h2>
            <div class="card">
                <div class="card-body">
                    <?php foreach ($checks['directories'] as $dir => $status): ?>
                        <div class="check-status <?php echo $status['exists'] && $status['writable'] ? 'check-success' : 'check-error'; ?>">
                            <h5><?php echo htmlspecialchars($dir); ?></h5>
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Exists</dt>
                                <dd class="col-sm-9"><?php echo $status['exists'] ? 'Yes' : 'No'; ?></dd>
                                
                                <dt class="col-sm-3">Writable</dt>
                                <dd class="col-sm-9"><?php echo $status['writable'] ? 'Yes' : 'No'; ?></dd>
                                
                                <dt class="col-sm-3">Permissions</dt>
                                <dd class="col-sm-9"><?php echo $status['permissions']; ?></dd>
                                
                                <dt class="col-sm-3">Owner</dt>
                                <dd class="col-sm-9"><?php echo $status['owner']; ?>:<?php echo $status['group']; ?></dd>
                            </dl>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- PHP Extensions -->
        <section class="mb-5">
            <h2>PHP Extensions</h2>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($checks['php_extensions'] as $ext => $loaded): ?>
                            <div class="col-md-4">
                                <div class="check-status <?php echo $loaded ? 'check-success' : 'check-error'; ?>">
                                    <?php echo htmlspecialchars($ext); ?>: 
                                    <?php echo $loaded ? 'Loaded' : 'Not Loaded'; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Database Connections -->
        <section class="mb-5">
            <h2>Database Connections</h2>
            <div class="card">
                <div class="card-body">
                    <!-- MySQL -->
                    <div class="check-status <?php echo $checks['mysql']['success'] ? 'check-success' : 'check-error'; ?>">
                        <h5>MySQL</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($checks['mysql']['message']); ?></p>
                    </div>

                    <!-- SQLite -->
                    <div class="check-status <?php echo $checks['sqlite']['success'] ? 'check-success' : 'check-error'; ?>">
                        <h5>SQLite</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($checks['sqlite']['message']); ?></p>
                        <?php if ($checks['sqlite']['success']): ?>
                            <small>Path: <?php echo htmlspecialchars($checks['sqlite']['path']); ?></small><br>
                            <small>File exists: <?php echo $checks['sqlite']['exists'] ? 'Yes' : 'No'; ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Required Files -->
        <section class="mb-5">
            <h2>Required Files</h2>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($checks['required_files'] as $file => $status): ?>
                            <div class="col-md-6">
                                <div class="check-status <?php echo $status['exists'] ? 'check-success' : 'check-error'; ?>">
                                    <h6><?php echo htmlspecialchars($file); ?></h6>
                                    <small>
                                        Exists: <?php echo $status['exists'] ? 'Yes' : 'No'; ?><br>
                                        Readable: <?php echo $status['readable'] ? 'Yes' : 'No'; ?><br>
                                        Size: <?php echo number_format($status['size']); ?> bytes
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 