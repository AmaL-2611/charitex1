<?php
session_start();
require_once 'config.php';

// Add this near the top after session_start()
// echo "<pre>";
// echo "Session data:\n";
// print_r($_SESSION);
// echo "</pre>";

// Check if orphanage is logged in
if (!isset($_SESSION['orphanage_id'])) {
    header('location:login_orphanage.php');
    exit();
}

// Initialize variables
$errors = [];
$success = '';
$edit_event = null;
$orphanage = null; // Initialize orphanage variable

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch orphanage details first
    $orphanage_stmt = $pdo->prepare("SELECT * FROM orphanage WHERE id = ?");
    $orphanage_stmt->execute([$_SESSION['orphanage_id']]);
    $orphanage = $orphanage_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orphanage) {
        // If orphanage not found, log out and redirect
        session_destroy();
        header('location:login_orphanage.php?error=Invalid session');
        exit();
    }

    // Add all missing columns to events table
    try {
        $pdo->exec("
            ALTER TABLE events 
            ADD COLUMN IF NOT EXISTS event_time TIME NOT NULL AFTER event_date,
            ADD COLUMN IF NOT EXISTS cause VARCHAR(100) NOT NULL AFTER event_time,
            ADD COLUMN IF NOT EXISTS created_by INT NOT NULL AFTER max_volunteers,
            ADD COLUMN IF NOT EXISTS created_by_type VARCHAR(20) NOT NULL DEFAULT 'admin' AFTER created_by,
            ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'active' AFTER created_by_type,
            ADD COLUMN IF NOT EXISTS description TEXT AFTER name,
            ADD COLUMN IF NOT EXISTS max_volunteers INT NOT NULL DEFAULT 50 AFTER location
        ");
    } catch (PDOException $e) {
        error_log("Table modification error: " . $e->getMessage());
    }

    // Create funding_requests table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS funding_requests (
            id INT PRIMARY KEY AUTO_INCREMENT,
            orphanage_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            goal_amount DECIMAL(10,2) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
            current_amount DECIMAL(10,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            approved_at TIMESTAMP NULL,
            FOREIGN KEY (orphanage_id) REFERENCES orphanage(id)
        )
    ");

} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
}

// Helper function for status badge colors
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        case 'completed': return 'info';
        default: return 'secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orphanage Dashboard - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Copy styles from admin_events.php */
        /* Reference lines 37-180 from admin_events.php for styling */
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="#dashboard">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_event.php">
                                <i class="fas fa-plus"></i> Create Event
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_events.php">
                                <i class="fas fa-calendar"></i> My Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_funding_request.php">
                                <i class="fas fa-hand-holding-usd"></i> Create Funding Request
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_funding_requests.php">
                                <i class="fas fa-list"></i> My Funding Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Welcome, <?php echo htmlspecialchars($orphanage['name'] ?? 'User'); ?></h1>
                </div>

                <!-- Display messages -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- You can add dashboard statistics or summary information here -->
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html> 