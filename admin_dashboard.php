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

// Add this function near the top of the file, after the require_once statement
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        case 'completed': return 'info';
        default: return 'secondary';
    }
}

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
    $donationStmt = $pdo->query("SELECT 
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
    $volunteerStmt = $pdo->query(" SELECT 
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
    $eventStmt = $pdo->query("SELECT 
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

    // Fetch all funding requests with orphanage details
    $funding_stmt = $pdo->query("SELECT 
            fr.*,
            o.name as orphanage_name,
            o.email as orphanage_email,
            o.phone as orphanage_phone
        FROM funding_requests fr
        JOIN orphanage o ON fr.orphanage_id = o.id
        ORDER BY 
            CASE fr.status 
                WHEN 'pending' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
                ELSE 4
            END,
            fr.created_at DESC
    ");
    $funding_requests = $funding_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
    error_log($error_message);
    $funding_requests = []; // Initialize as empty array if query fails
}

// Handle request approval/rejection
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'approve' || $action === 'reject') {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $stmt = $pdo->prepare("
                UPDATE funding_requests 
                SET status = ?, 
                    approved_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$status, $request_id]);
            
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error updating request: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }
}

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['volunteer_id'])) {
    try {
        $volunteer_id = $_POST['volunteer_id'];
        $action = $_POST['action'];
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $stmt = $pdo->prepare("UPDATE volunteers SET status = ? WHERE id = ?");
        $stmt->execute([$status, $volunteer_id]);

        // Send email notification to volunteer
        $email_stmt = $pdo->prepare("SELECT email, name FROM volunteers WHERE id = ?");
        $email_stmt->execute([$volunteer_id]);
        $volunteer = $email_stmt->fetch();

        if ($volunteer) {
            $to = $volunteer['email'];
            $subject = "Volunteer Application " . ucfirst($status);
            $message = "Dear " . $volunteer['name'] . ",\n\n";
            $message .= "Your volunteer application has been " . $status . ".\n";
            if ($status === 'approved') {
                $message .= "You can now login to your account and start volunteering.\n";
            } else {
                $message .= "Unfortunately, we cannot accept your application at this time.\n";
            }
            $message .= "\nBest regards,\nCharitex Team";
            
            mail($to, $subject, $message);
        }

        $_SESSION['message'] = "Volunteer successfully " . $status;
    } catch (PDOException $e) {
        error_log("Error updating volunteer status: " . $e->getMessage());
        $_SESSION['error'] = "Error updating volunteer status";
    }
    
    header('Location: admin_dashboard.php');
    exit();
}

// Fetch pending volunteer applications
try {
    $stmt = $pdo->prepare("
        SELECT id, name, email, mobile, location, availability, aadhar_file, police_doc, created_at, status 
        FROM volunteers 
        WHERE status = 'pending'
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $pending_volunteers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching pending volunteers: " . $e->getMessage());
    $pending_volunteers = [];
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
                        <a class="nav-link" href="volunteer_requests.php">
                            <i class="fas fa-user-clock"></i> Volunteer Requests
                            <?php if($pendingVolunteers > 0): ?>
                                <span class="badge bg-danger"><?php echo $pendingVolunteers; ?></span>
                            <?php endif; ?>
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
                    <li class="nav-item">
                        <a class="nav-link" href="admin_reports.php">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#funding-requests">
                            <i class="fas fa-hand-holding-usd"></i> Funding Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="review_beneficiaries.php">
                            <i class="fas fa-user-check"></i> Review Beneficiaries
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

                    <!-- Add this section in the main content area -->
                    <section id="funding-requests" class="mt-5">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="mb-0">Orphanage Funding Requests</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Orphanage</th>
                                                <th>Title</th>
                                                <th>Goal Amount</th>
                                                <th>Duration</th>
                                                <th>Image</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch all funding requests with orphanage details
                                            try {
                                                $stmt = $pdo->query("
                                                    SELECT fr.*, o.name as orphanage_name, o.email as orphanage_email 
                                                    FROM funding_requests fr
                                                    JOIN orphanage o ON fr.orphanage_id = o.id
                                                    ORDER BY fr.created_at DESC
                                                ");
                                                $funding_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($funding_requests as $request):
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?php echo htmlspecialchars($request['orphanage_name']); ?>
                                                        <small class="d-block text-muted"><?php echo htmlspecialchars($request['orphanage_email']); ?></small>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($request['title']); ?></strong>
                                                        <small class="d-block text-muted">Created: <?php echo date('d M Y', strtotime($request['created_at'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div>₹<?php echo number_format($request['goal_amount'], 2); ?></div>
                                                        <small class="text-muted">Current: ₹<?php echo number_format($request['current_amount'], 2); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            echo date('d M Y', strtotime($request['start_date'])) . ' - <br>' . 
                                                                 date('d M Y', strtotime($request['end_date'])); 
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <img src="<?php echo htmlspecialchars($request['image_path']); ?>" 
                                                             alt="Request Image" 
                                                             style="max-width: 100px; height: auto;"
                                                             class="img-thumbnail">
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo getStatusBadgeClass($request['status']); ?>">
                                                            <?php echo ucfirst($request['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-info mb-1" 
                                                                onclick="viewRequestDetails(<?php echo $request['id']; ?>)">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <?php if ($request['status'] === 'pending'): ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-success mb-1" 
                                                                    onclick="updateRequestStatus(<?php echo $request['id']; ?>, 'approve')">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-danger mb-1" 
                                                                    onclick="updateRequestStatus(<?php echo $request['id']; ?>, 'reject')">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php 
                                                endforeach;
                                            } catch (PDOException $e) {
                                                echo '<tr><td colspan="7" class="text-center text-danger">Error loading funding requests</td></tr>';
                                                error_log("Error fetching funding requests: " . $e->getMessage());
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Request Details Modal -->
                        <div class="modal fade" id="requestDetailsModal" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Funding Request Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" id="requestDetailsContent">
                                        <!-- Content will be loaded dynamically -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Add this section in the main content area -->
                    <section id="volunteer-requests" class="mt-5">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="mb-0">Volunteer Verification Requests</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Submission Date</th>
                                                <th>Verification Form</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT * FROM volunteer_verifications WHERE status='pending' ORDER BY submission_date DESC";
                                            $result = $pdo->query($sql);
                                            
                                            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<tr>";
                                                
                                                echo "<td>".$row['email']."</td>";
                                               
                                                echo "<td>".date('d M Y H:i', strtotime($row['submission_date']))."</td>";
                                                echo "<td>
                                                        <a href='".$row['document_path']."' class='btn btn-sm btn-primary' download>
                                                            <i class='fas fa-download'></i> Download Form
                                                        </a>
                                                        <button type='button' class='btn btn-sm btn-info ms-1' 
                                                                onclick='viewDocument(\"".$row['document_path']."\")'>
                                                            <i class='fas fa-eye'></i> View
                                                        </button>
                                                      </td>";
                                                echo "<td>
                                                        <button type='button' class='btn btn-sm btn-success' 
                                                                data-bs-toggle='modal' 
                                                                data-bs-target='#approveModal".$row['id']."'>
                                                            <i class='fas fa-check'></i> Approve
                                                        </button>
                                                        <button type='button' class='btn btn-sm btn-danger ms-1' 
                                                                onclick='rejectVolunteer(".$row['id'].")'>
                                                            <i class='fas fa-times'></i> Reject
                                                        </button>
                                                      </td>";
                                                echo "</tr>";
                                            }
                                            
                                            if ($result->rowCount() == 0) {
                                                echo "<tr><td colspan='6' class='text-center'>No pending verification requests</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <!-- Approval Modals -->
    <?php
    $sql = "SELECT * FROM volunteer_verifications WHERE status='pending'";
    $result = $pdo->query($sql);
    
    while($row = $result->fetch(PDO::FETCH_ASSOC)) 
    {
        echo "<div class='modal fade' id='approveModal".$row['id']."' tabindex='-1'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title'>Generate Verification Code</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                        </div>
                        <div class='modal-body'>
                            <form id='approvalForm".$row['id']."'>
                                <div class='mb-3'>
                                    <label for='verification_code' class='form-label'>Enter Verification Code</label>
                                    <input type='text' class='form-control' name='verification_code' required 
                                           placeholder='Enter code to send to volunteer'>
                                </div>
                                <input type='hidden' name='volunteer_id' value='".$row['id']."'>
                                <input type='hidden' name='volunteer_email' value='".$row['email']."'>
                            </form>
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                            <button type='button' class='btn btn-primary' onclick='approveWithCode(".$row['id'].");'>
                                Approve & Send Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>";
    }
    ?>

    <!-- Document Preview Modal -->
    <div class="modal fade" id="documentPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <iframe id="documentFrame" style="width: 100%; height: 500px;" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function viewRequestDetails(requestId) {
        // Fetch request details
        fetch(`get_request_details.php?id=${requestId}`)
            .then(response => response.json())
            .then(data => {
                const content = document.getElementById('requestDetailsContent');
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <img src="${data.image_path}" class="img-fluid mb-3" alt="Request Image">
                        </div>
                        <div class="col-md-6">
                            <h4>${data.title}</h4>
                            <p><strong>Orphanage:</strong> ${data.orphanage_name}</p>
                            <p><strong>Goal Amount:</strong> ₹${data.goal_amount}</p>
                            <p><strong>Current Amount:</strong> ₹${data.current_amount}</p>
                            <p><strong>Duration:</strong> ${data.start_date} to ${data.end_date}</p>
                            <p><strong>Status:</strong> <span class="badge bg-${getStatusBadgeClass(data.status)}">${data.status}</span></p>
                            <p><strong>Description:</strong></p>
                            <p>${data.description}</p>
                        </div>
                    </div>
                `;
                new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading request details');
            });
    }

    function updateRequestStatus(requestId, action) {
        if (!confirm(`Are you sure you want to ${action} this request?`)) {
            return;
        }

        fetch('update_request_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `request_id=${requestId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating request status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating request status');
        });
    }

    function getStatusBadgeClass(status) {
        switch (status) {
            case 'pending': return 'warning';
            case 'approved': return 'success';
            case 'rejected': return 'danger';
            case 'completed': return 'info';
            default: return 'secondary';
        }
    }

    function approveWithCode(id) {
        console.log('approveWithCode called with id:', id); // Debug log
        
        const form = document.getElementById('approvalForm' + id);
        const codeInput = form.querySelector('input[name="verification_code"]');
        
        if (!codeInput.value.trim()) {
            alert('Please enter a verification code');
            return;
        }
        
        const formData = new FormData(form);
        
        // Debug log
        console.log('Form data:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        $.ajax({
            url: 'approve_volunteer.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                console.log('Sending AJAX request...'); // Debug log
            },
            success: function(response) {
                console.log('Response received:', response); // Debug log
                
                if(response.includes('success')) {
                    alert('Verification code has been sent successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                alert('Error occurred while processing the request.');
            }
        });
    }

    function rejectVolunteer(id) {
        if(confirm('Are you sure you want to reject this volunteer?')) {
            $.ajax({
                url: 'reject_volunteer.php',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    alert('Volunteer rejected');
                    location.reload();
                }
            });
        }
    }

    function viewDocument(path) {
        const frame = document.getElementById('documentFrame');
        frame.src = path;
        new bootstrap.Modal(document.getElementById('documentPreviewModal')).show();
    }

    // Add this to check if jQuery is loaded
    $(document).ready(function() {
        console.log('jQuery is loaded and document is ready');
    });
    </script>
</body>
</html>