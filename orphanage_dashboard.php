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

    // First, let's see what columns we have
    $columns = $pdo->query("SHOW COLUMNS FROM funding_requests")->fetchAll(PDO::FETCH_COLUMN);
    
    // For debugging
    // echo "<pre>"; print_r($columns); echo "</pre>";
    
    // Then use the correct column name
    $stmt = $pdo->prepare("SELECT COUNT(*) as count 
                          FROM funding_requests 
                          WHERE orphanage_id = :id");
    $stmt->execute(['id' => $_SESSION['orphanage_id']]);
    $result = $stmt->fetch();
    $fundingRequestCount = $result['count'];

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
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orphanage Dashboard - CHARITEX</title>
    
    <!-- Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Existing CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2ecc71;
            --accent: #e74c3c;
            --background: #f8f9fa;
            --surface: #ffffff;
            --text: #2c3e50;
            --border: #dee2e6;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
        }

        /* Modern Sidebar */
        .sidebar {
            background: var(--surface);
            box-shadow: 2px 0 10px var(--shadow);
            height: 100vh;
            transition: all 0.3s ease;
        }

        .nav-link {
            color: var(--text);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link:hover {
            background: rgba(52, 152, 219, 0.1);
            transform: translateX(5px);
        }

        .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px var(--shadow);
        }

        /* Dashboard Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }

        /* Loading Spinner */
        .loading-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 5px 15px var(--shadow);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .fab:hover {
            transform: scale(1.1);
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Add these styles */
        .table {
            font-size: 0.9rem;
        }

        .badge {
            padding: 0.5em 1em;
            font-weight: 500;
        }

        .dashboard-section {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.02);
            cursor: pointer;
        }

        .section-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .section-header h3 {
            margin: 0;
            color: #2c3e50;
        }

        .section-header i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner"></div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4>CHARITEX</h4>
                        <p class="text-muted">Orphanage Portal</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register_beneficiary.php">
                                <i class="fas fa-user-plus"></i> Add Beneficiary
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="requested_beneficiaries.php" class="nav-link">
                                <i class="fas fa-user-clock"></i>
                                <span>Requested Beneficiaries</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_event.php">
                                <i class="fas fa-calendar-plus"></i> Create Event
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_events.php">
                                <i class="fas fa-calendar"></i> My Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_funding_request.php">
                                <i class="fas fa-hand-holding-usd"></i> Funding Request
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="active_funding_requests.php">
                                <i class="fas fa-money-bill-wave"></i> View Funding Requests
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

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Welcome Banner -->
                <div class="welcome-banner fade-in">
                    <h1>Welcome, <?php echo htmlspecialchars($orphanage['name'] ?? 'User'); ?></h1>
                    <p>Manage your beneficiaries, events, and funding requests</p>
                </div>

                <!-- Dashboard Stats -->
                <div class="stats-container fade-in">
                    <?php
                    // Fetch statistics
                    $stats = [
                        'beneficiaries' => $pdo->query("SELECT COUNT(*) FROM beneficiaries WHERE orphanage_id = " . $_SESSION['orphanage_id'])->fetchColumn(),
                        'funding' => $fundingRequestCount
                    ];
                    ?>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--primary);">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3><?php echo $stats['beneficiaries']; ?></h3>
                        <p class="text-muted">Total Beneficiaries</p>
                    </div>
                    <!-- <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: var(--secondary);">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <h3><?php echo $stats['events']; ?></h3>
                        <p class="text-muted">Active Events</p>
                    </div> -->
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(231, 76, 60, 0.1); color: var(--accent);">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h3><?php echo $stats['funding']; ?></h3>
                        <p class="text-muted">Funding Requests</p>
                    </div>
                </div>

                <!-- Existing Content -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger fade-in">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success fade-in">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Add this section for displaying beneficiaries -->
                <div id="requested-beneficiaries" class="dashboard-section" style="display: none;">
                    <div class="section-header">
                        <h3><i class="fas fa-user-clock"></i> Requested Beneficiaries</h3>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Support Type</th>
                                            <th>Required Amount</th>
                                            <th>Status</th>
                                            <th>Requested Date</th>
                                            <th>Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Fetch beneficiaries for the current orphanage
                                        $stmt = $pdo->prepare("
                                            SELECT * FROM beneficiaries 
                                            WHERE orphanage_id = :orphanage_id 
                                            ORDER BY created_at DESC
                                        ");
                                        $stmt->execute(['orphanage_id' => $_SESSION['orphanage_id']]);
                                        $beneficiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($beneficiaries as $beneficiary): 
                                            // Define status badge color
                                            $statusClass = match($beneficiary['status']) {
                                                'Approved' => 'bg-success',
                                                'Rejected' => 'bg-danger',
                                                'Pending' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($beneficiary['full_name']) ?></td>
                                                <td>
                                                    <?php if($beneficiary['support_type'] == 'Education'): ?>
                                                        <i class="fas fa-graduation-cap"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-hospital"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($beneficiary['support_type']) ?>
                                                </td>
                                                <td>₹<?= number_format($beneficiary['required_amount'], 2) ?></td>
                                                <td>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= htmlspecialchars($beneficiary['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($beneficiary['created_at'])) ?></td>
                                                <td><?= date('d M Y', strtotime($beneficiary['updated_at'] ?? $beneficiary['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if(empty($beneficiaries)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No beneficiaries found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fab" data-bs-toggle="tooltip" title="Quick Actions">
        <i class="fas fa-plus"></i>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Show loading spinner during AJAX requests
        function showLoading() {
            document.querySelector('.loading-spinner').style.display = 'flex';
        }

        function hideLoading() {
            document.querySelector('.loading-spinner').style.display = 'none';
        }

        // Add loading spinner to all forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                showLoading();
            });
        });

        // Hide loading spinner when page is fully loaded
        window.addEventListener('load', hideLoading);

        function showRequestedBeneficiaries() {
            // Hide all other sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show requested beneficiaries section
            document.getElementById('requested-beneficiaries').style.display = 'block';
            
            // Update active menu item
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html> 