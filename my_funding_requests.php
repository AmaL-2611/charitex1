<?php
session_start();
require_once 'config.php';

// Check if orphanage is logged in
if (!isset($_SESSION['orphanage_id'])) {
    header('location:login_orphanage.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch existing funding requests for this orphanage
    $funding_stmt = $pdo->prepare("
        SELECT 
            fr.*,
            o.name as orphanage_name
        FROM funding_requests fr
        JOIN orphanage o ON fr.orphanage_id = o.id
        WHERE fr.orphanage_id = :orphanage_id
        ORDER BY fr.created_at DESC
    ");

    $funding_stmt->execute([':orphanage_id' => $_SESSION['orphanage_id']]);
    $funding_requests = $funding_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $error_message = "An error occurred while fetching the funding requests.";
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
    <title>My Funding Requests - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Funding Requests</h2>
            <a href="create_funding_request.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Request
            </a>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Goal Amount</th>
                            <th>Current Amount</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($funding_requests)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No funding requests found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($funding_requests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['title']); ?></td>
                                    <td>₹<?php echo number_format($request['goal_amount'], 2); ?></td>
                                    <td>₹<?php echo number_format($request['current_amount'], 2); ?></td>
                                    <td><?php echo date('d M Y', strtotime($request['end_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getStatusBadgeClass($request['status']); ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <div class="btn-group">
                                                <a href="edit_funding_request.php?id=<?php echo $request['id']; ?>" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="confirmDelete(<?php echo $request['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-3">
            <a href="orphanage_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmDelete(requestId) {
        if (confirm('Are you sure you want to delete this funding request?')) {
            window.location.href = `delete_funding_request.php?id=${requestId}`;
        }
    }
    </script>
</body>
</html> 