<?php
// Database Configuration
$host = 'localhost';     // Database host (usually localhost)
$dbname = 'charitex1';    // Your database name
$username = 'root';  // Database username
$password = '';  // Database password

// Ensure the function is only defined once
if (!function_exists('getDatabaseConnection')) {
    /**
     * Establishes a PDO database connection
     * 
     * @return PDO|false Database connection object or false on failure
     */
    function getDatabaseConnection() {
        try {
            // Create PDO connection
            $pdo = new PDO("mysql:host=$GLOBALS[host];dbname=$GLOBALS[dbname]", $GLOBALS['username'], $GLOBALS['password']);
            
            // Set error mode to exception
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $pdo;
        } catch (PDOException $e) {
            // Log the error
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Return false or handle the error as needed
            return false;
        }
    }
}

// Create global database connection
try {
    $pdo = getDatabaseConnection();

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

    $pdo->exec($createDonorTable);
    $pdo->exec($createVolunteerTable);

} catch (PDOException $e) {
    error_log("Database Initialization Error: " . $e->getMessage());
}

// Only process registration if all required fields are present
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_POST['name']) && 
    isset($_POST['email']) && 
    isset($_POST['mobile']) && 
    isset($_POST['role']) && 
    isset($_POST['password'])) {
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $role = $_POST['role'];
    $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email exists in either donor or volunteer table
    $check_donor = $pdo->prepare("SELECT email FROM donors WHERE email = ?");
    $check_donor->execute([$email]);
    $donor_email_exists = $check_donor->rowCount();

    $check_volunteer = $pdo->prepare("SELECT email FROM volunteers WHERE email = ?");
    $check_volunteer->execute([$email]);
    $volunteer_email_exists = $check_volunteer->rowCount();

    // Check if mobile exists
    $check_donor_mobile = $pdo->prepare("SELECT mobile FROM donors WHERE mobile = ?");
    $check_donor_mobile->execute([$mobile]);
    $donor_mobile_exists = $check_donor_mobile->rowCount();

    $check_volunteer_mobile = $pdo->prepare("SELECT mobile FROM volunteers WHERE mobile = ?");
    $check_volunteer_mobile->execute([$mobile]);
    $volunteer_mobile_exists = $check_volunteer_mobile->rowCount();

    if ($donor_email_exists > 0 || $volunteer_email_exists > 0) {
        header("Location: signup.php?error=email_exists");
        exit();
    }

    if ($donor_mobile_exists > 0 || $volunteer_mobile_exists > 0) {
        header("Location: signup.php?error=mobile_exists");
        exit();
    }

    if ($role === 'donor') {
        $stmt = $pdo->prepare("INSERT INTO donors (name, email, mobile, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $mobile, $hashedPassword]);
    } elseif ($role === 'volunteer') {
        $location = isset($_POST['location']) ? $_POST['location'] : '';
        $availability = isset($_POST['availability']) ? $_POST['availability'] : '';
        $supportEducation = isset($_POST['supportEducation']) ? 1 : 0;
        $supportOrphans = isset($_POST['supportOrphans']) ? 1 : 0;
        $supportElders = isset($_POST['supportElders']) ? 1 : 0;
        $skills = isset($_POST['skills']) ? $_POST['skills'] : '';

        $stmt = $pdo->prepare("
            INSERT INTO volunteers 
            (name, email, mobile, password, location, availability, 
            support_education, support_orphans, support_elders, skills) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name, $email, $mobile, $hashedPassword, $location, $availability,
            $supportEducation, $supportOrphans, $supportElders, $skills
        ]);
    }

    // header("Location: login.php?success=registered");
    // exit();
}
?>