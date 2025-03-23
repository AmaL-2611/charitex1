<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
  header('Location: login.php');
  exit();
}

// Initialize variables
$errors = [];
$success = '';

// Establish database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Add this for debugging
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
}

// Update the approval handling code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $beneficiary_id = $_POST['beneficiary_id'] ?? '';
    $action = $_POST['action'] ?? '';
    $remarks = $_POST['remarks'] ?? '';

    if ($beneficiary_id && $action) {
        try {
            $status = ($action === 'approve') ? 'Approved' : 'Rejected';
            
            // Debug query
            echo "Status being set to: " . $status;
            
            $stmt = $pdo->prepare(" UPDATE beneficiaries 
                SET status = :status, 
                    admin_remarks = :remarks,
                    reviewed_at = NOW(),
                    reviewed_by = :admin_id
                WHERE id = :beneficiary_id
            ");
            
            $params = [
                ':status' => $status,
                ':remarks' => $remarks,
                ':admin_id' => $_SESSION['admin_id'],
                ':beneficiary_id' => $beneficiary_id
            ];
            
            if ($stmt->execute($params)) {
                $success = "Beneficiary successfully " . strtolower($status);
            } else {
                $errors[] = "Failed to update status";
                // Debug database errors
                print_r($stmt->errorInfo());
            }
        } catch (PDOException $e) {
            $errors[] = "Error updating status: " . $e->getMessage();
        }
    }
}

// Fetch pending beneficiaries
try {
    $stmt = $pdo->prepare(" SELECT b.*, o.name as orphanage_name 
        FROM beneficiaries b 
        JOIN orphanage o ON b.orphanage_id = o.id 
        WHERE b.status = 'Active' 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
    $beneficiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Error fetching beneficiaries: " . $e->getMessage();
}

// Add this to your PHP processing section at the top of the file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'stop_support') {
    $beneficiary_id = $_POST['beneficiary_id'] ?? '';
    $stop_reason = $_POST['stop_reason'] ?? '';

    if ($beneficiary_id && $stop_reason) {
        try {
            // Update the status and add stop reason
            $stmt = $pdo->prepare("
                UPDATE beneficiaries 
                SET 
                    status = 'Stopped',
                    stop_reason = :stop_reason,
                    stopped_at = NOW()
                WHERE id = :beneficiary_id
            ");

            $result = $stmt->execute([
                ':beneficiary_id' => $beneficiary_id,
                ':stop_reason' => $stop_reason
            ]);

            if ($result) {
                $_SESSION['success_message'] = "Support has been stopped for this beneficiary.";
            } else {
                $_SESSION['error_message'] = "Failed to stop support. Please try again.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        }
        
        // Redirect to refresh the page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Add this to show success/error messages
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['success_message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['error_message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Beneficiaries - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .document-link {
            text-decoration: none;
            color: #0d6efd;
        }
        .document-link:hover {
            text-decoration: underline;
        }
        /* Add this CSS to make sure the modal appears above everything */
        .modal {
            z-index: 1050;
        }
        .table {
            font-size: 0.9rem;
        }
        .btn-sm {
            font-size: 0.8rem;
        }
        .text-success {
            font-size: 0.9rem;
        }
        h3 {
            color: #0d6efd;
            margin-top: 2rem;
        }
        .border-bottom {
            border-color: #dee2e6 !important;
        }
        /* Add these styles to ensure modal works properly */
        .modal-backdrop {
            z-index: 1040;
        }
        .btn:disabled {
            cursor: not-allowed;
        }
        .beneficiary-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            cursor: pointer;
        }
        /* Add these styles */
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
        .btn-warning:hover {
            background-color: #ffb300;
            border-color: #ffb300;
            color: #000;
        }
        .modal-header.bg-warning {
            background-color: #ffc107;
        }
        .alert {
            margin-bottom: 20px;
        }
        .alert-dismissible .btn-close {
            padding: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include your admin sidebar here -->
            
            <main class="col-md-12">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Review Beneficiaries</h1>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Orphanage</th>
                                <th>Support Type</th>
                                <th>Documents</th>
                                <th>Photo</th>
                                <th>Submitted Date</th>
                                <th>Required Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($beneficiaries as $beneficiary): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($beneficiary['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($beneficiary['orphanage_name']); ?></td>
                                    <td><?php echo htmlspecialchars($beneficiary['support_type']); ?></td>
                                    <td>
                                        <?php if ($beneficiary['support_type'] === 'Education'): ?>
                                            <?php if ($beneficiary['admission_letter_path']): ?>
                                                <a href="<?php echo htmlspecialchars($beneficiary['admission_letter_path']); ?>" class="document-link" target="_blank">
                                                    <i class="fas fa-file-alt"></i> Admission Letter
                                                </a><br>
                                            <?php endif; ?>
                                            
                                            <?php if ($beneficiary['fee_structure_path']): ?>
                                                <a href="<?php echo htmlspecialchars($beneficiary['fee_structure_path']); ?>" class="document-link" target="_blank">
                                                    <i class="fas fa-file-invoice-dollar"></i> Fee Structure
                                                </a><br>
                                            <?php endif; ?>
                                            
                                            <?php if ($beneficiary['report_card_path']): ?>
                                                <a href="<?php echo htmlspecialchars($beneficiary['report_card_path']); ?>" class="document-link" target="_blank">
                                                    <i class="fas fa-file-alt"></i> Report Card
                                                </a><br>
                                            <?php endif; ?>
                                            
                                            <?php if ($beneficiary['recommendation_letter_path']): ?>
                                                <a href="<?php echo htmlspecialchars($beneficiary['recommendation_letter_path']); ?>" class="document-link" target="_blank">
                                                    <i class="fas fa-file-signature"></i> Recommendation Letter
                                                </a><br>
                                            <?php endif; ?>
                                            
                                            <?php if ($beneficiary['guardian_id_path']): ?>
                                                <a href="<?php echo htmlspecialchars($beneficiary['guardian_id_path']); ?>" class="document-link" target="_blank">
                                                    <i class="fas fa-id-card"></i> Guardian's ID
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($beneficiary['profile_image']): ?>
                                            <img src="<?php echo htmlspecialchars($beneficiary['profile_image']); ?>" 
                                                 alt="Beneficiary Photo" 
                                                 class="beneficiary-img"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#imageModal<?php echo $beneficiary['id']; ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($beneficiary['created_at'])); ?></td>
                                    <td>₹<?php echo number_format($beneficiary['required_amount'], 2); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="setAction('approve', <?php echo $beneficiary['id']; ?>)" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#actionModal<?php echo $beneficiary['id']; ?>">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="setAction('reject', <?php echo $beneficiary['id']; ?>)" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#actionModal<?php echo $beneficiary['id']; ?>">
                                            <i class="fas fa-times"></i> Reject
                                        </button>

                                        <!-- Modal for this beneficiary -->
                                        <div class="modal fade" id="actionModal<?php echo $beneficiary['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" id="actionForm<?php echo $beneficiary['id']; ?>">
                                                        <input type="hidden" name="beneficiary_id" value="<?php echo $beneficiary['id']; ?>">
                                                        <input type="hidden" name="action" id="actionInput<?php echo $beneficiary['id']; ?>">
                                                        
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Confirm Action</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>Required Amount:</strong> ₹<?php echo number_format($beneficiary['required_amount'], 2); ?></p>
                                                            <div class="mb-3">
                                                                <label for="remarks<?php echo $beneficiary['id']; ?>" class="form-label">Remarks</label>
                                                                <textarea class="form-control" id="remarks<?php echo $beneficiary['id']; ?>" 
                                                                        name="remarks" rows="3" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Confirm</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Approved Requests Section -->
                <div class="mt-4">
                    <h3 class="border-bottom pb-2">Approved Requests</h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Orphanage</th>
                                    <th>Support Type</th>
                                    <th>Documents</th>
                                    <th>Approved Date</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Fetch approved beneficiaries
                                $stmt = $pdo->prepare("
                                    SELECT b.*, o.name as orphanage_name 
                                    FROM beneficiaries b 
                                    JOIN orphanage o ON b.orphanage_id = o.id 
                                    WHERE b.status = 'Approved' 
                                    ORDER BY b.reviewed_at DESC
                                ");
                                $stmt->execute();
                                $approved_beneficiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($approved_beneficiaries as $beneficiary): 
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($beneficiary['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($beneficiary['orphanage_name']); ?></td>
                                        <td><?php echo htmlspecialchars($beneficiary['support_type']); ?></td>
                                        <td>
                                            <?php if ($beneficiary['admission_letter_path']): ?>
                                                <a href="<?php echo htmlspecialchars($beneficiary['admission_letter_path']); ?>" class="btn btn-sm btn-outline-primary mb-1" target="_blank">
                                                    <i class="fas fa-file-alt"></i> Admission Letter
                                                </a><br>
                                            <?php endif; ?>
                                            <?php if ($beneficiary['fee_structure_path']): ?>
                                                <a href="<?php echo htmlspecialchars($beneficiary['fee_structure_path']); ?>" class="btn btn-sm btn-outline-primary mb-1" target="_blank">
                                                    <i class="fas fa-file-invoice-dollar"></i> Fee Structure
                                                </a><br>
                                            <?php endif; ?>
                                            <?php if ($beneficiary['report_card_path']): ?>
                                                <a href="<?php echo htmlspecialchars($beneficiary['report_card_path']); ?>" class="btn btn-sm btn-outline-primary mb-1" target="_blank">
                                                    <i class="fas fa-file-alt"></i> Report Card
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($beneficiary['reviewed_at'])); ?></td>
                                        <td>
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i>
                                                <?php echo htmlspecialchars($beneficiary['admin_remarks'] ?? 'Approved'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($beneficiary['status'] === 'Approved'): ?>
                                                <button type="button" 
                                                        class="btn btn-warning btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#stopSupportModal<?php echo $beneficiary['id']; ?>">
                                                    <i class="fas fa-stop-circle"></i> Stop Support
                                                </button>

                                                <!-- Stop Support Modal -->
                                                <div class="modal fade" id="stopSupportModal<?php echo $beneficiary['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST">
                                                                <input type="hidden" name="beneficiary_id" value="<?php echo $beneficiary['id']; ?>">
                                                                <input type="hidden" name="action" value="stop_support">
                                                                
                                                                <div class="modal-header bg-warning text-dark">
                                                                    <h5 class="modal-title">
                                                                        <i class="fas fa-exclamation-triangle"></i> Stop Support Confirmation
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                
                                                                <div class="modal-body">
                                                                    <p class="mb-3">Are you sure you want to stop support for this beneficiary?</p>
                                                                    <div class="mb-3">
                                                                        <label for="stop_reason<?php echo $beneficiary['id']; ?>" class="form-label">
                                                                            Reason for Stopping Support
                                                                        </label>
                                                                        <textarea class="form-control" 
                                                                                id="stop_reason<?php echo $beneficiary['id']; ?>" 
                                                                                name="stop_reason" 
                                                                                rows="3" 
                                                                                required></textarea>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-warning">
                                                                        <i class="fas fa-stop-circle"></i> Confirm Stop Support
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function setAction(action, beneficiaryId) {
        document.getElementById('actionInput' + beneficiaryId).value = action;
    }

    // Add this to handle form submission
    document.addEventListener('DOMContentLoaded', function() {
        // Get all modals
        const modals = document.querySelectorAll('.modal');
        
        // Initialize Bootstrap modals
        modals.forEach(modal => {
            new bootstrap.Modal(modal);
        });

        // Handle form submissions
        const forms = document.querySelectorAll('form[id^="actionForm"]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Submit the form
                fetch(window.location.href, {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(response => response.text())
                .then(() => {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(this.closest('.modal'));
                    modal.hide();
                    
                    // Reload the page to show updated status
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            });
        });

        const stopSupportForms = document.querySelectorAll('form[action*="stop_support"]');
        stopSupportForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const reason = this.querySelector('textarea[name="stop_reason"]').value.trim();
                if (!reason) {
                    e.preventDefault();
                    alert('Please provide a reason for stopping support.');
                }
            });
        });
    });
    </script>
</body>
</html> 