<?php
require_once 'config.php';
require_once 'connect.php';

try 
{
    // Create PDO connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Drop tables in the correct order to avoid foreign key constraints
    $drop_tables = [
        "event_registrations",
        "events"
    ];

    foreach ($drop_tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table");
        } catch (PDOException $e) {
            echo "Could not drop table $table: " . $e->getMessage() . "\n";
        }
    }

    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Create events table
    $pdo->exec("CREATE TABLE events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        event_date DATE NOT NULL,
        location VARCHAR(255),
        max_volunteers INT DEFAULT 50,
        current_volunteers INT DEFAULT 0,
        status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert sample events
    $pdo->exec("INSERT INTO events (name, description, event_date, location) VALUES 
        ('Community Cleanup Drive', 'Help clean up local parks and streets', '2024-03-15', 'City Central Park'),
        ('Food Bank Support', 'Assist in sorting and packing food donations', '2024-03-22', 'Local Food Bank'),
        ('Charity Marathon', 'Volunteer for annual charity run event', '2024-04-05', 'City Stadium')
    ");

    // Create event_registrations table
    $pdo->exec("CREATE TABLE event_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        volunteer_id INT NOT NULL,
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('registered', 'confirmed', 'waitlisted', 'cancelled') DEFAULT 'registered',
        role VARCHAR(100),
        additional_notes TEXT,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE,
        UNIQUE KEY unique_registration (event_id, volunteer_id)
    )");

    echo "Event registrations table created successfully!";

} catch (PDOException $e) {
    // Handle any errors
    echo "Error creating event registrations table: " . $e->getMessage();
    // Log the error to a file
    error_log("Event Registrations Table Creation Error: " . $e->getMessage(), 3, "error.log");
} finally {
    // Ensure foreign key checks are re-enabled even if an error occurs
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}
?>
