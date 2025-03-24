<?php
// Database Configuration
$host = 'localhost';     // Database host (usually localhost)
$dbname = 'charitex1';    // Your database name
$username = 'root';  // Database username
$password = '';  // Database password

// Create database connection
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);



// Set PDO to throw exceptions on error
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>