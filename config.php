<?php
<<<<<<< HEAD
define('DB_HOST', 'localhost');
define('DB_NAME', 'charitex1');
define('DB_USER', 'root');
define('DB_PASS', ''); 
=======
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'charitex1');

// Attempt to connect to MySQL database
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}

// Create orphanage event registrations table if it doesn't exist
$sql = "
    CREATE TABLE IF NOT EXISTS orphanage_event_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        orphanage_id INT NOT NULL,
        event_id INT NOT NULL,
        registration_date DATETIME NOT NULL,
        FOREIGN KEY (orphanage_id) REFERENCES orphanage(id),
        FOREIGN KEY (event_id) REFERENCES events(id),
        UNIQUE KEY unique_registration (orphanage_id, event_id)
    );
";

// if ($conn->multi_query($sql) === TRUE) {
//     echo "Orphanage event registrations table created successfully";
// } else {
//     echo "Error creating orphanage event registrations table: " . $conn->error;
// }

$conn->close();
>>>>>>> master
?>