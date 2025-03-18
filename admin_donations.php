<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Initialize variables
$error_message = '';
$donations = [];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$totalPages = 1;
$donors = [];
$causes = [];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get total count for pagination
    $stmt = $pdo->query("SELECT COUNT(*) FROM donations");
    $total_records = $stmt->fetchColumn();
    $totalPages = ceil($total_records / $limit);

    // Get donations with pagination
    $stmt = $pdo->prepare("
        SELECT 
            d.id,
            d.amount,
            d.payment_status,
            d.payment_method,
            d.created_at,
            dn.name as donor_name,
            dn.email as donor_email,
            c.title as cause_title
        FROM donations d
        LEFT JOIN donors dn ON d.donor_id = dn.id
        LEFT JOIN causes c ON d.cause_id = c.id
        ORDER BY d.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get donors for offline donation form
    $donorStmt = $pdo->query("SELECT id, name, email FROM donors ORDER BY name");
    $donors = $donorStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get causes for offline donation form
    $causeStmt = $pdo->query("SELECT id, title FROM causes WHERE status = 'active' ORDER BY title");
    $causes = $causeStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        try {
            $stmt = $pdo->prepare("
                UPDATE donations 
                SET payment_status = :status 
                WHERE id = :donation_id
            ");
            $stmt->execute([
                ':status' => $_POST['status'],
                ':donation_id' => $_POST['donation_id']
            ]);
            header('Location: admin_donations.php?success=Status updated successfully');
            exit();
        } catch (PDOException $e) {
            $error_message = "Error updating status: " . $e->getMessage();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'add_offline') {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO donations (donor_id, cause_id, amount, payment_status, payment_method) 
                VALUES (:donor_id, :cause_id, :amount, 'completed', 'offline')
            ");
            $stmt->execute([
                ':donor_id' => $_POST['donor_id'],
                ':cause_id' => $_POST['cause_id'],
                ':amount' => $_POST['amount']
            ]);
            header('Location: admin_donations.php?success=Offline donation added');
            exit();
        } catch (PDOException $e) {
            $error_message = "Error adding offline donation: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Management - CHARITEX Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f5f5f5;
            --text-color: #333333;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
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
                            <a class="nav-link active" href="admin_donations.php">
                                <i class="fas fa-hand-holding-usd"></i> Donations
                            </a>
                        </li>
                        <!-- Add other sidebar items -->
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Donation Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfflineDonationModal">
                        <i class="fas fa-plus"></i> Add Offline Donation
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Donations Table -->
                <div class="card">
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Donor</th>
                                        <th>Cause</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($donations)): ?>
                                        <tr>
                                            <td colspan="8" class="no-data">No donations found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($donations as $donation): ?>
                                        <tr>
                                            <td><?php echo $donation['id']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($donation['donor_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($donation['donor_email']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($donation['cause_title']); ?></td>
                                            <td>$<?php echo number_format($donation['amount'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $donation['payment_status'] === 'completed' ? 'success' : 
                                                        ($donation['payment_status'] === 'pending' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo ucfirst($donation['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo ucfirst($donation['payment_method']); ?></td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($donation['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form method="POST">
                                                                <input type="hidden" name="action" value="update_status">
                                                                <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="dropdown-item text-success">
                                                                    <i class="fas fa-check"></i> Mark as Completed
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST">
                                                                <input type="hidden" name="action" value="update_status">
                                                                <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                                                <input type="hidden" name="status" value="pending">
                                                                <button type="submit" class="dropdown-item text-warning">
                                                                    <i class="fas fa-clock"></i> Mark as Pending
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Offline Donation Modal -->
    <div class="modal fade" id="addOfflineDonationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Offline Donation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_offline">
                        
                        <div class="mb-3">
                            <label class="form-label">Donor</label>
                            <select name="donor_id" class="form-select" required>
                                <option value="">Select Donor</option>
                                <?php foreach ($donors as $donor): ?>
                                <option value="<?php echo $donor['id']; ?>">
                                    <?php echo htmlspecialchars($donor['name']); ?> (<?php echo htmlspecialchars($donor['email']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cause</label>
                            <select name="cause_id" class="form-select" required>
                                <option value="">Select Cause</option>
                                <?php foreach ($causes as $cause): ?>
                                <option value="<?php echo $cause['id']; ?>">
                                    <?php echo htmlspecialchars($cause['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Donation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
