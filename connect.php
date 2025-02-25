<?php
// Database Configuration
$host = 'localhost';     // Database host (usually localhost)
$dbname = 'charitex1';    // Your database name
$username = 'root';  // Database username
$password = '';  // Database password

// Create database connection
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

// Database Table Creation (if not already exists)
$createDonorTable = "CREATE TABLE IF NOT EXISTS donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mobile VARCHAR(15) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$createVolunteerTable = "CREATE TABLE IF NOT EXISTS volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mobile VARCHAR(15) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    location VARCHAR(100),
    availability VARCHAR(50),
    support_education BOOLEAN DEFAULT FALSE,
    support_orphans BOOLEAN DEFAULT FALSE,
    support_elders BOOLEAN DEFAULT FALSE,
    skills TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$createEventsTable = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(100) NOT NULL,
    cause VARCHAR(50) NOT NULL,
    max_volunteers INT NOT NULL DEFAULT 50,
    current_volunteers INT NOT NULL DEFAULT 0,
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$pdo->exec($createDonorTable);
$pdo->exec($createVolunteerTable);
$pdo->exec($createEventsTable);

// Set PDO to throw exceptions on error
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>