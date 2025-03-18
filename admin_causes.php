<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php?error=Unauthorized access');
    exit();
}

// Initialize database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/causes/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    // Predefined cause types
                    $causeTypes = [
                        'education' => 'Educational Support',
                        'orphan_care' => 'Orphan Care',
                        'elder_support' => 'Elder Support'
                    ];

                    // Validate cause type
                    if (!isset($causeTypes[$_POST['type']])) {
                        header('Location: admin_causes.php?error=Invalid cause type');
                        exit();
                    }

                    // Check for existing cause with the same type
                    $stmt = $pdo->prepare("SELECT * FROM causes WHERE title LIKE ?");
                    $stmt->execute(["%{$causeTypes[$_POST['type']]}%"]);
                    $existingCause = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existingCause) {
                        header('Location: admin_causes.php?error=' . urlencode("A {$causeTypes[$_POST['type']]} cause is already ongoing. Please complete the existing cause first."));
                        exit();
                    }

                    $image_url = '';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($fileExtension, $allowedExtensions)) {
                            $fileName = uniqid() . '.' . $fileExtension;
                            $uploadPath = $uploadDir . $fileName;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                                $image_url = $uploadPath;
                            }
                        }
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO causes (title, description, goal_amount, image_url) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $causeTypes[$_POST['type']] . " Cause",
                        $_POST['description'],
                        $_POST['goal_amount'],
                        $image_url
                    ]);
                    header('Location: admin_causes.php?success=Cause created successfully');
                    exit();
                    break;

                case 'update':
                    // Predefined cause types
                    $causeTypes = [
                        'education' => 'Educational Support',
                        'orphan_care' => 'Orphan Care',
                        'elder_support' => 'Elder Support'
                    ];

                    // Validate cause type
                    if (!isset($causeTypes[$_POST['type']])) {
                        header('Location: admin_causes.php?error=Invalid cause type');
                        exit();
                    }

                    // Check for existing cause with the same type (excluding the current cause)
                    $stmt = $pdo->prepare("SELECT * FROM causes WHERE title LIKE ? AND id != ?");
                    $stmt->execute(["%{$causeTypes[$_POST['type']]}%", $_POST['cause_id']]);
                    $existingCause = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existingCause) {
                        header('Location: admin_causes.php?error=' . urlencode("A {$causeTypes[$_POST['type']]} cause is already ongoing. Please complete the existing cause first."));
                        exit();
                    }

                    $image_url = $_POST['current_image_url'];
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($fileExtension, $allowedExtensions)) {
                            $fileName = uniqid() . '.' . $fileExtension;
                            $uploadPath = $uploadDir . $fileName;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                                // Delete old image if it exists
                                if (!empty($_POST['current_image_url']) && file_exists($_POST['current_image_url'])) {
                                    unlink($_POST['current_image_url']);
                                }
                                $image_url = $uploadPath;
                            }
                        }
                    }

                    $stmt = $pdo->prepare("
                        UPDATE causes 
                        SET title = ?, description = ?, goal_amount = ?, status = ?, image_url = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $causeTypes[$_POST['type']] . " Cause",
                        $_POST['description'],
                        $_POST['goal_amount'],
                        $_POST['status'],
                        $image_url,
                        $_POST['cause_id']
                    ]);
                    header('Location: admin_causes.php?success=Cause updated successfully');
                    exit();
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM causes WHERE id = ?");
                    $stmt->execute([$_POST['cause_id']]);
                    header('Location: admin_causes.php?success=Cause deleted successfully');
                    exit();
                    break;
            }
        }
    }

    // Get all causes with their donation totals
    $stmt = $pdo->query("
        SELECT 
            c.*,
            COALESCE(SUM(d.amount), 0) as total_donations,
            COUNT(DISTINCT d.donor_id) as donor_count
        FROM causes c
        LEFT JOIN donations d ON c.id = d.cause_id
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    $causes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
}

// Get current theme preference
$darkMode = isset($_COOKIE['admin_theme']) && $_COOKIE['admin_theme'] === 'dark';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cause Management - CHARITEX Admin</title>
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
        
        .progress {
            height: 10px;
        }
        
        .cause-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
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
                            <a class="nav-link active" href="admin_causes.php">
                                <i class="fas fa-heart"></i> Causes
                            </a>
                        </li>
                        <!-- Add other sidebar items -->
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Cause Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCauseModal">
                        <i class="fas fa-plus"></i> New Cause
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Causes Grid -->
                <div class="row">
                    <?php foreach ($causes as $cause): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <?php if (!empty($cause['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($cause['image_url']); ?>" class="cause-image mb-3 w-100" alt="Cause Image">
                                <?php endif; ?>
                                
                                <h5 class="card-title"><?php echo htmlspecialchars($cause['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($cause['description']); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-primary">
                                        <?php 
                                        $causeTypes = [
                                            'Educational Support Cause' => 'Educational Support',
                                            'Orphan Care Cause' => 'Orphan Care',
                                            'Elder Support Cause' => 'Elder Support'
                                        ];
                                        echo isset($causeTypes[$cause['title']]) ? $causeTypes[$cause['title']] : 'Unspecified'; 
                                        ?>
                                    </span>
                                    <span class="badge bg-<?php 
                                        switch($cause['status']) {
                                            case 'completed': echo 'success'; break;
                                            case 'inactive': echo 'warning'; break;
                                            default: echo 'primary';
                                        }
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst($cause['status'])); ?>
                                    </span>
                                </div>
                                
                                <div class="progress mb-2" style="height: 10px;">
                                    <?php 
                                    $progressPercentage = $cause['goal_amount'] > 0 ? 
                                        min(100, round(($cause['total_donations'] / $cause['goal_amount']) * 100, 2)) : 0; 
                                    ?>
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $progressPercentage; ?>%" 
                                         aria-valuenow="<?php echo $progressPercentage; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo $progressPercentage; ?>%
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <small class="text-muted">Goal: $<?php echo number_format($cause['goal_amount'], 2); ?></small>
                                    <small class="text-muted">Raised: $<?php echo number_format($cause['total_donations'], 2); ?></small>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCauseModal<?php echo $cause['id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this cause?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="cause_id" value="<?php echo $cause['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Cause Modal -->
                    <div class="modal fade" id="editCauseModal<?php echo $cause['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Cause</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cause_id" value="<?php echo $cause['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Title</label>
                                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($cause['title']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($cause['description']); ?></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Goal Amount</label>
                                            <input type="number" class="form-control" name="goal_amount" value="<?php echo $cause['goal_amount']; ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Type</label>
                                            <select class="form-select" name="type" required>
                                                <option value="education" <?php echo $cause['title'] === 'Educational Support Cause' ? 'selected' : ''; ?>>Educational Support</option>
                                                <option value="orphan_care" <?php echo $cause['title'] === 'Orphan Care Cause' ? 'selected' : ''; ?>>Orphan Care</option>
                                                <option value="elder_support" <?php echo $cause['title'] === 'Elder Support Cause' ? 'selected' : ''; ?>>Elder Support</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Image</label>
                                            <input type="file" class="form-control" name="image" accept="image/*">
                                            <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($cause['image_url']); ?>">
                                            <?php if (!empty($cause['image_url'])): ?>
                                            <div class="mt-2">
                                                <small class="text-muted">Current image:</small>
                                                <img src="<?php echo htmlspecialchars($cause['image_url']); ?>" class="mt-1" style="max-width: 100px; height: auto;">
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="active" <?php echo $cause['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="completed" <?php echo $cause['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="inactive" <?php echo $cause['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- New Cause Modal -->
    <div class="modal fade" id="newCauseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Cause</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Goal Amount</label>
                            <input type="number" class="form-control" name="goal_amount" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required>
                                <option value="education">Educational Support</option>
                                <option value="orphan_care">Orphan Care</option>
                                <option value="elder_support">Elder Support</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Cause</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
