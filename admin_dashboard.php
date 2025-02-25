<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Initialize variables
$totalDonors = 0;
$totalVolunteers = 0;
$totalDonations = 0;
$activeCauses = 0;
$upcomingEvents = 0;
$pendingVolunteers = 0;
$recentActivities = [];
$error_message = '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get total donors
    $stmt = $pdo->query("SELECT COUNT(*) FROM donors");
    $totalDonors = $stmt->fetchColumn();

    // Get total volunteers
    $stmt = $pdo->query("SELECT COUNT(*) FROM volunteers");
    $totalVolunteers = $stmt->fetchColumn();

    // Get total donations amount
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = 'completed'");
    $totalDonations = $stmt->fetchColumn();

    // Get active causes count
    $stmt = $pdo->query("SELECT COUNT(*) FROM causes WHERE status = 'active'");
    $activeCauses = $stmt->fetchColumn();

    // Get upcoming events count
    $stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE start_date > NOW()");
    $upcomingEvents = $stmt->fetchColumn();

    // Get pending volunteer applications
    $stmt = $pdo->query("SELECT COUNT(*) FROM volunteers WHERE status = 'pending'");
    $pendingVolunteers = $stmt->fetchColumn();

    // Get recent activities
    $recentActivities = [];
    
    // Get recent donations
    $donationStmt = $pdo->query("
        SELECT 
            'donation' as type,
            d.created_at,
            CONCAT(COALESCE(dn.name, 'Anonymous'), ' donated $', d.amount) as description,
            c.title as related_item,
            d.payment_status as status
        FROM donations d
        LEFT JOIN donors dn ON d.donor_id = dn.id
        LEFT JOIN causes c ON d.cause_id = c.id
        ORDER BY d.created_at DESC
        LIMIT 3
    ");
    $recentActivities = array_merge($recentActivities, $donationStmt->fetchAll(PDO::FETCH_ASSOC));

    // Get recent volunteers
    $volunteerStmt = $pdo->query("
        SELECT 
            'volunteer' as type,
            created_at,
            CONCAT(name, ' joined as volunteer') as description,
            '' as related_item,
            status
        FROM volunteers
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $recentActivities = array_merge($recentActivities, $volunteerStmt->fetchAll(PDO::FETCH_ASSOC));

    // Get recent events
    $eventStmt = $pdo->query("
        SELECT 
            'event' as type,
            created_at,
            CONCAT(title, ' event created') as description,
            location as related_item,
            status
        FROM events
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $recentActivities = array_merge($recentActivities, $eventStmt->fetchAll(PDO::FETCH_ASSOC));

    // Sort all activities by date
    usort($recentActivities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Limit to 5 most recent activities
    $recentActivities = array_slice($recentActivities, 0, 5);

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
    error_log($error_message);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CHARITEX Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #343a40;
            --secondary-color: #495057;
            --accent-color: #0d6efd;
        }
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: var(--primary-color);
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar .nav-link:hover {
            background-color: var(--secondary-color);
        }
        .sidebar .nav-link.active {
            background-color: var(--accent-color);
        }
        .content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card {
            border-left: 4px solid var(--accent-color);
        }
        .activity-item {
            padding: 10px;
            border-left: 3px solid var(--accent-color);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <h3 class="text-white">CHARITEX</h3>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_users.php">
                            <i class="fas fa-users"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_causes.php">
                            <i class="fas fa-hand-holding-heart"></i> Causes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_donations.php">
                            <i class="fas fa-donate"></i> Donations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_volunteers.php">
                            <i class="fas fa-user-friends"></i> Volunteers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_events.php">
                            <i class="fas fa-calendar-alt"></i> Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_feedback.php">
                            <i class="fas fa-comments"></i> Feedback
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="admin_reports.php">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a> -->
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
              
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="content">
                    <div class="d-flex justify-content-between align-items-center my-4">
                        <h2>Dashboard Overview</h2>
                    </div>

                    <!-- Stats Cards Row 1 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Donations</h5>
                                    <p class="card-text h3">$<?php echo number_format($totalDonations, 2); ?></p>
                                    <small class="text-muted">Lifetime donations received</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h5 class="card-title">Active Causes</h5>
                                    <p class="card-text h3"><?php echo $activeCauses; ?></p>
                                    <small class="text-muted">Current fundraising campaigns</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Users</h5>
                                    <p class="card-text h3"><?php echo $totalDonors + $totalVolunteers; ?></p>
                                    <small class="text-muted">Combined donors and volunteers</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h5 class="card-title">Upcoming Events</h5>
                                    <p class="card-text h3"><?php echo $upcomingEvents; ?></p>
                                    <small class="text-muted">Scheduled activities</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards Row 2 -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Recent Activities</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($recentActivities)): ?>
                                        <?php foreach ($recentActivities as $activity): ?>
                                            <div class="activity-item">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <i class="fas fa-<?php echo $activity['type'] === 'donation' ? 'donate' : ($activity['type'] === 'volunteer' ? 'user' : 'calendar'); ?>"></i>
                                                        <?php echo htmlspecialchars($activity['description']); ?>
                                                    </div>
                                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></small>
                                                </div>
                                                <?php if ($activity['related_item']): ?>
                                                    <small class="text-muted d-block">
                                                        <?php echo htmlspecialchars($activity['related_item']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted">
                                            <p>No recent activities found</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="admin_causes.php?action=new" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Create New Cause
                                        </a>
                                        <a href="admin_events.php?action=new" class="btn btn-success">
                                            <i class="fas fa-calendar-plus"></i> Schedule Event
                                        </a>
                                        <a href="admin_volunteers.php?filter=pending" class="btn btn-warning">
                                            <i class="fas fa-user-clock"></i> Review Volunteer Applications (<?php echo $pendingVolunteers; ?>)
                                        </a>
                                        <a href="admin_reports.php" class="btn btn-info">
                                            <i class="fas fa-file-alt"></i> Generate Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>