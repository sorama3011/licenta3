<?php
// Database connection configuration
$db_host = "localhost";
$db_name = "gusturi_romanesti";
$db_user = "root";
$db_pass = "";

// Establish database connection
try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Get schema for each table
    $schema = [];
    foreach ($tables as $table) {
        $columns = $conn->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $schema[$table] = $columns;
    }
    
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Schema - Gusturi Românești</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .table-container {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Database Schema: <?php echo htmlspecialchars($db_name); ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <h4 class="alert-heading">Connection Error</h4>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <h4 class="alert-heading">Connection Successful</h4>
                <p>Successfully connected to database: <strong><?php echo htmlspecialchars($db_name); ?></strong></p>
                <p class="mb-0">Found <?php echo count($tables); ?> tables</p>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="list-group">
                        <div class="list-group-item list-group-item-primary">Database Tables</div>
                        <?php foreach ($tables as $table): ?>
                            <a href="#table-<?php echo htmlspecialchars($table); ?>" class="list-group-item list-group-item-action">
                                <?php echo htmlspecialchars($table); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <?php foreach ($schema as $table => $columns): ?>
                        <div class="table-container" id="table-<?php echo htmlspecialchars($table); ?>">
                            <h3><?php echo htmlspecialchars($table); ?></h3>
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Field</th>
                                        <th>Type</th>
                                        <th>Null</th>
                                        <th>Key</th>
                                        <th>Default</th>
                                        <th>Extra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($columns as $column): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($column['Field']); ?></td>
                                            <td><?php echo htmlspecialchars($column['Type']); ?></td>
                                            <td><?php echo htmlspecialchars($column['Null']); ?></td>
                                            <td><?php echo htmlspecialchars($column['Key']); ?></td>
                                            <td><?php echo $column['Default'] !== null ? htmlspecialchars($column['Default']) : '<em>NULL</em>'; ?></td>
                                            <td><?php echo htmlspecialchars($column['Extra']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <?php
                            // Get sample data
                            try {
                                $sampleData = $conn->query("SELECT * FROM `$table` LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($sampleData)):
                            ?>
                                <h5>Sample Data</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-secondary">
                                            <tr>
                                                <?php foreach (array_keys($sampleData[0]) as $header): ?>
                                                    <th><?php echo htmlspecialchars($header); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sampleData as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $value): ?>
                                                        <td><?php echo $value !== null ? htmlspecialchars($value) : '<em>NULL</em>'; ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php 
                                endif;
                            } catch (Exception $e) {
                                echo '<div class="alert alert-warning">Could not fetch sample data: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                            
                            <hr>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>