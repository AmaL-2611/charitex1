<?php
session_start();
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php?error=Unauthorized access');
    exit();
}

// Initialize variables
$users = [];
$error_message = '';
$success_message = '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Function to send deactivation email
    function sendDeactivationEmail($recipientEmail) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'amalbabu740@gmail.com';
            $mail->Password   = 'dezu oetk vhpz wktz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('amalbabu740@gmail.com', 'CHARITEX');
            $mail->addAddress($recipientEmail);
            $mail->Subject = 'Account Deactivation Notice';
            $mail->Body    = "Dear User,\n\nYour account has been deactivated by the administrator. If you believe this was done in error, please contact our support team.\n\nBest regards,\nCHARITEX Team";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }

    // Handle user status updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            $userType = $_POST['user_type'];
            $table = ($userType === 'donor') ? 'donors' : 'volunteers';
            
            switch ($_POST['action']) {
                case 'activate':
                    $stmt = $pdo->prepare("UPDATE $table SET status = 'active' WHERE id = ?");
                    $stmt->execute([$userId]);
                    $success_message = "User activated successfully";
                    break;
                    
                case 'deactivate':
                    $stmt = $pdo->prepare("UPDATE $table SET status = 'inactive' WHERE id = ?");
                    $stmt->execute([$userId]);
                    $success_message = "User account has been deactivated";
                    $stmt = $pdo->prepare("SELECT email FROM $table WHERE id = ?");
                    $stmt->execute([$userId]);
                    $userEmail = $stmt->fetchColumn();
                    sendDeactivationEmail($userEmail);
                    break;
            }
        }
    }

    // Get all users (both donors and volunteers)
    $users = [];
    
    // Get donors
    $donorStmt = $pdo->query("
        SELECT 
            id,
            name,
            email,
            COALESCE(phone, 'Not provided') as phone,
            'donor' as user_type,
            status,
            created_at,
            (SELECT COUNT(*) FROM donations WHERE donor_id = donors.id) as contribution_count,
            (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE donor_id = donors.id) as total_contribution
        FROM donors
        ORDER BY name
    ");
    $donors = $donorStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get volunteers
    $volunteerStmt = $pdo->query("
        SELECT 
            id,
            name,
            email,
            COALESCE(phone, 'Not provided') as phone,
            'volunteer' as user_type,
            status,
            created_at,
            (SELECT COUNT(*) FROM event_participants WHERE volunteer_id = volunteers.id) as event_count,
            (SELECT COALESCE(SUM(hours_logged), 0) FROM event_participants WHERE volunteer_id = volunteers.id) as total_hours
        FROM volunteers
        ORDER BY name
    ");
    $volunteers = $volunteerStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine and sort users
    $users = array_merge($donors, $volunteers);
    usort($users, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
    error_log($error_message);
}

// Get current theme preference
$darkMode = isset($_COOKIE['admin_theme']) && $_COOKIE['admin_theme'] === 'dark';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CHARITEX Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: <?php echo $darkMode ? '#1a1a1a' : '#f5f5f5'; ?>;
            --text-color: <?php echo $darkMode ? '#ffffff' : '#333333'; ?>;
            --card-bg: <?php echo $darkMode ? '#2d2d2d' : '#ffffff'; ?>;
            --border-color: <?php echo $darkMode ? '#404040' : '#e0e0e0'; ?>;
        }
        
        body {
            background: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }
        
        .table {
            color: var(--text-color);
        }
        
        .table td, .table th {
            border-color: var(--border-color);
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #495057;
        }

        .badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_users.php">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <!-- Add other sidebar items -->
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>User Management</h1>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Contact</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Contribution</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="7" class="no-data">No users found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-2">
                                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <?php echo htmlspecialchars($user['name']); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($user['email']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['user_type'] === 'donor' ? 'primary' : 'success'; ?>">
                                                    <?php echo ucfirst($user['user_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $user['status'] === 'active' ? 'success' : 'danger'; 
                                                ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['user_type'] === 'donor'): ?>
                                                    <?php echo $user['contribution_count']; ?> donations<br>
                                                    <small class="text-muted">
                                                        Total: $<?php echo number_format($user['total_contribution'], 2); ?>
                                                    </small>
                                                <?php else: ?>
                                                    <?php echo $user['event_count']; ?> events<br>
                                                    <small class="text-muted">
                                                        <?php echo number_format($user['total_hours'], 1); ?> hours
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <?php if ($user['status'] === 'active'): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <input type="hidden" name="user_type" value="<?php echo $user['user_type']; ?>">
                                                                <input type="hidden" name="action" value="deactivate">
                                                                <button type="submit" class="dropdown-item text-warning">
                                                                    <i class="fas fa-user-slash"></i> Deactivate
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <input type="hidden" name="user_type" value="<?php echo $user['user_type']; ?>">
                                                                <input type="hidden" name="action" value="activate">
                                                                <button type="submit" class="dropdown-item text-success">
                                                                    <i class="fas fa-user-check"></i> Activate
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
