<?php
require_once 'connect.php';
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get table columns
    $stmt = $pdo->query("SHOW COLUMNS FROM events");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns in events table:\n";
    foreach ($columns as $column) {
        echo "Column Name: " . $column['Field'] . "\n";
        echo "Type: " . $column['Type'] . "\n";
        echo "Null: " . $column['Null'] . "\n";
        echo "Key: " . $column['Key'] . "\n";
        echo "Default: " . ($column['Default'] ?? 'NULL') . "\n";
        echo "Extra: " . $column['Extra'] . "\n";
        echo "-------------------\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
