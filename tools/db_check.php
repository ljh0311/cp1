<?php
require_once 'inc/config.php';
require_once 'database/DatabaseManager.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

function testMySQLConnection() {
    try {
        $host = DB_HOST;
        $dbname = DB_NAME;
        $username = DB_USER;
        $password = DB_PASS;

        $conn = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return [
            'success' => true,
            'message' => 'MySQL connection successful',
            'connection' => $conn
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'MySQL connection failed: ' . $e->getMessage()
        ];
    }
}

function testSQLiteConnection() {
    try {
        $dbPath = __DIR__ . '/database/fallback.db';
        $sqlite = new SQLite3($dbPath);
        return [
            'success' => true,
            'message' => 'SQLite connection successful',
            'connection' => $sqlite
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'SQLite connection failed: ' . $e->getMessage()
        ];
    }
}

function getTableData($connection, $isMySQL = true) {
    $tables = [];
    
    try {
        if ($isMySQL) {
            $query = "SHOW TABLES";
            $result = $connection->query($query);
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tableName = $row[0];
                $tables[$tableName] = [
                    'name' => $tableName,
                    'rows' => []
                ];
                
                // Get table data
                $dataQuery = "SELECT * FROM $tableName LIMIT 5";
                $dataResult = $connection->query($dataQuery);
                $tables[$tableName]['rows'] = $dataResult->fetchAll(PDO::FETCH_ASSOC);
                
                // Get column information
                $columnsQuery = "DESCRIBE $tableName";
                $columnsResult = $connection->query($columnsQuery);
                $tables[$tableName]['columns'] = $columnsResult->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            $query = "SELECT name FROM sqlite_master WHERE type='table'";
            $result = $connection->query($query);
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $tableName = $row['name'];
                $tables[$tableName] = [
                    'name' => $tableName,
                    'rows' => []
                ];
                
                // Get table data
                $dataQuery = "SELECT * FROM $tableName LIMIT 5";
                $dataResult = $connection->query($dataQuery);
                $rows = [];
                while ($dataRow = $dataResult->fetchArray(SQLITE3_ASSOC)) {
                    $rows[] = $dataRow;
                }
                $tables[$tableName]['rows'] = $rows;
                
                // Get column information
                $columnsQuery = "PRAGMA table_info($tableName)";
                $columnsResult = $connection->query($columnsQuery);
                $columns = [];
                while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
                    $columns[] = [
                        'Field' => $column['name'],
                        'Type' => $column['type'],
                        'Null' => $column['notnull'] ? 'NO' : 'YES',
                        'Key' => $column['pk'] ? 'PRI' : '',
                        'Default' => $column['dflt_value']
                    ];
                }
                $tables[$tableName]['columns'] = $columns;
            }
        }
        return $tables;
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Database Diagnostic - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
    <style>
        .connection-status {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .success {
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
        }
        .table-info {
            margin-bottom: 2rem;
        }
        .table-name {
            background-color: #e9ecef;
            padding: 0.5rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .data-table {
            margin-bottom: 2rem;
        }
        .schema-table {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Database Diagnostic</h1>

        <!-- MySQL Connection Test -->
        <section class="mb-5">
            <h2>MySQL Connection Test</h2>
            <?php
            $mysqlTest = testMySQLConnection();
            $mysqlClass = $mysqlTest['success'] ? 'success' : 'error';
            ?>
            <div class="connection-status <?php echo $mysqlClass; ?>">
                <?php echo $mysqlTest['message']; ?>
            </div>

            <?php if ($mysqlTest['success']): ?>
                <div class="table-info">
                    <h3>MySQL Database Structure and Data</h3>
                    <?php
                    $mysqlTables = getTableData($mysqlTest['connection'], true);
                    foreach ($mysqlTables as $table):
                    ?>
                        <div class="table-name">
                            <h4><?php echo htmlspecialchars($table['name']); ?></h4>
                        </div>

                        <!-- Table Schema -->
                        <div class="schema-table">
                            <h5>Schema</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Type</th>
                                            <th>Null</th>
                                            <th>Key</th>
                                            <th>Default</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($table['columns'] as $column): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($column['Field']); ?></td>
                                                <td><?php echo htmlspecialchars($column['Type']); ?></td>
                                                <td><?php echo htmlspecialchars($column['Null']); ?></td>
                                                <td><?php echo htmlspecialchars($column['Key']); ?></td>
                                                <td><?php echo htmlspecialchars($column['Default'] ?? 'NULL'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Table Data -->
                        <div class="data-table">
                            <h5>Sample Data (First 5 rows)</h5>
                            <?php if (empty($table['rows'])): ?>
                                <div class="alert alert-info">No data available in table</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <?php foreach (array_keys($table['rows'][0]) as $column): ?>
                                                    <th><?php echo htmlspecialchars($column); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($table['rows'] as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $value): ?>
                                                        <td><?php echo htmlspecialchars($value); ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- SQLite Connection Test -->
        <section class="mb-5">
            <h2>SQLite Connection Test</h2>
            <?php
            $sqliteTest = testSQLiteConnection();
            $sqliteClass = $sqliteTest['success'] ? 'success' : 'error';
            ?>
            <div class="connection-status <?php echo $sqliteClass; ?>">
                <?php echo $sqliteTest['message']; ?>
            </div>

            <?php if ($sqliteTest['success']): ?>
                <div class="table-info">
                    <h3>SQLite Database Structure and Data</h3>
                    <?php
                    $sqliteTables = getTableData($sqliteTest['connection'], false);
                    foreach ($sqliteTables as $table):
                        if (isset($table['error'])) continue;
                    ?>
                        <div class="table-name">
                            <h4><?php echo htmlspecialchars($table['name']); ?></h4>
                        </div>

                        <!-- Table Schema -->
                        <div class="schema-table">
                            <h5>Schema</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Type</th>
                                            <th>Null</th>
                                            <th>Key</th>
                                            <th>Default</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($table['columns'] as $column): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($column['Field']); ?></td>
                                                <td><?php echo htmlspecialchars($column['Type']); ?></td>
                                                <td><?php echo htmlspecialchars($column['Null']); ?></td>
                                                <td><?php echo htmlspecialchars($column['Key']); ?></td>
                                                <td><?php echo htmlspecialchars($column['Default'] ?? 'NULL'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Table Data -->
                        <div class="data-table">
                            <h5>Sample Data (First 5 rows)</h5>
                            <?php if (empty($table['rows'])): ?>
                                <div class="alert alert-info">No data available in table</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <?php foreach (array_keys($table['rows'][0]) as $column): ?>
                                                    <th><?php echo htmlspecialchars($column); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($table['rows'] as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $value): ?>
                                                        <td><?php echo htmlspecialchars($value); ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <?php require_once 'inc/footer.inc.php'; ?>
</body>
</html> 