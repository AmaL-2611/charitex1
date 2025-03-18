<?php
$host = 'localhost';
$dbname = 'charitex';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host", $username, $password);
    
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    
    // Select the database
    $pdo->exec("USE $dbname");
    
    // Create users table
    $createTableQuery = "CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('donor', 'volunteer') NOT NULL,
        location VARCHAR(100),
        availability ENUM('weekdays', 'weekends', 'flexible'),
        skills TEXT,
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($createTableQuery);
    
    echo "Database and Users table created successfully!";
    
} catch(PDOException $e) {
    die("Database creation failed: " . $e->getMessage());
}
?>
