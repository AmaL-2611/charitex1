<?php
require_once 'connect.php';
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SHOW COLUMNS FROM volunteers");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Columns in volunteers table:\n";
    print_r($columns);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
