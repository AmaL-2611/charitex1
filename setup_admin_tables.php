<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Array to store results
    $results = [];

    // Create Donors table
    $sql = "CREATE TABLE IF NOT EXISTS donors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(20),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    $results[] = "✅ Donors table created successfully";

    // Create Volunteers table
    $sql = "CREATE TABLE IF NOT EXISTS volunteers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(20),
        skills TEXT,
        availability TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    $results[] = "✅ Volunteers table created successfully";

    // Add status column to donors table if it doesn't exist
    $sql = "ALTER TABLE donors ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active' AFTER phone";
    $pdo->exec($sql);
    $results[] = "✅ Added status column to donors table";

    // Add status column to volunteers table if it doesn't exist
    $sql = "ALTER TABLE volunteers ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active' AFTER availability";
    $pdo->exec($sql);
    $results[] = "✅ Added status column to volunteers table";

    // Create Causes table
    $sql = "CREATE TABLE IF NOT EXISTS causes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        goal_amount DECIMAL(10,2) NOT NULL,
        current_amount DECIMAL(10,2) DEFAULT 0,
        image_url VARCHAR(255),
        status ENUM('active', 'completed', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    $results[] = "✅ Causes table created successfully";

    // Create Donations table
    $sql = "CREATE TABLE IF NOT EXISTS donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        donor_id INT,
        cause_id INT,
        amount DECIMAL(10,2) NOT NULL,
        payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        payment_method VARCHAR(50),
        transaction_id VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (donor_id) REFERENCES donors(id),
        FOREIGN KEY (cause_id) REFERENCES causes(id)
    )";
    $pdo->exec($sql);
    $results[] = "✅ Donations table created successfully";

    // Create Events table
    $sql = "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        location VARCHAR(255),
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        coordinator_id INT,
        status ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (coordinator_id) REFERENCES volunteers(id)
    )";
    $pdo->exec($sql);
    $results[] = "✅ Events table created successfully";

    // Create Event Participants table
    $sql = "CREATE TABLE IF NOT EXISTS event_participants (
        event_id INT,
        volunteer_id INT,
        status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
        hours_logged DECIMAL(5,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (event_id, volunteer_id),
        FOREIGN KEY (event_id) REFERENCES events(id),
        FOREIGN KEY (volunteer_id) REFERENCES volunteers(id)
    )";
    $pdo->exec($sql);
    $results[] = "✅ Event Participants table created successfully";

    // Create Feedback table
    $sql = "CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        user_type ENUM('donor', 'volunteer') NOT NULL,
        subject VARCHAR(255),
        message TEXT,
        status ENUM('pending', 'resolved') DEFAULT 'pending',
        admin_response TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        resolved_at TIMESTAMP NULL
    )";
    $pdo->exec($sql);
    $results[] = "✅ Feedback table created successfully";

    // Create Admin Activity Logs table
    $sql = "CREATE TABLE IF NOT EXISTS admin_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT,
        action VARCHAR(255) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admin_users(id)
    )";
    $pdo->exec($sql);
    $results[] = "✅ Admin Logs table created successfully";

    // Create Notifications table
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        user_type ENUM('admin', 'donor', 'volunteer') NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    $results[] = "✅ Notifications table created successfully";

    // Success message
    $success = true;
    $message = "All tables created successfully!";

} catch (PDOException $e) {
    $success = false;
    $message = "Database Error: " . $e->getMessage();
    $results[] = "❌ Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin Tables - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            padding: 20px;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .result-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .result-item.success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .result-item.error {
            background: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1 class="mb-4">CHARITEX Admin Tables Setup</h1>
        
        <?php if ($success): ?>
        <div class="alert alert-success mb-4">
            <?php echo $message; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-danger mb-4">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div class="results">
            <h3>Setup Results:</h3>
            <?php foreach ($results as $result): ?>
            <div class="result-item <?php echo strpos($result, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $result; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4">
            <a href="admin_dashboard.php" class="btn btn-primary">Go to Admin Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
