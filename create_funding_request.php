<?php
session_start();
require_once 'config.php';

// Check if orphanage is logged in
if (!isset($_SESSION['orphanage_id'])) {
    header('location:login_orphanage.php');
    exit();
}

$errors = [];
$success = '';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle funding request creation
    if (isset($_POST['create_funding_request'])) {
        $title = trim($_POST['request_title'] ?? '');
        $description = trim($_POST['request_description'] ?? '');
        $goal_amount = floatval($_POST['goal_amount'] ?? 0);
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';

        // Validate inputs
        if (empty($title)) $errors[] = "Title is required";
        if (empty($description)) $errors[] = "Description is required";
        if ($goal_amount < 1000) $errors[] = "Goal amount must be at least ₹1,000";
        if (empty($start_date) || empty($end_date)) $errors[] = "Both start and end dates are required";
        if (strtotime($end_date) <= strtotime($start_date)) {
            $errors[] = "End date must be after start date";
        }

        // Handle image upload
        if (isset($_FILES['request_image']) && $_FILES['request_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/funding_requests/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_ext = strtolower(pathinfo($_FILES['request_image']['name'], PATHINFO_EXTENSION));
            $unique_filename = uniqid() . '_' . time() . '.' . $file_ext;
            $file_path = $upload_dir . $unique_filename;

            // Validate image
            $allowed_types = ['jpg', 'jpeg', 'png'];
            if (!in_array($file_ext, $allowed_types)) {
                $errors[] = "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
            } else {
                if (!move_uploaded_file($_FILES['request_image']['tmp_name'], $file_path)) {
                    $errors[] = "Failed to upload image.";
                }
            }
        } else {
            $errors[] = "Image is required";
        }

        // If no errors, insert the funding request
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO funding_requests (
                        orphanage_id, title, description, goal_amount, 
                        start_date, end_date, image_path, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
                
                $stmt->execute([
                    $_SESSION['orphanage_id'],
                    $title,
                    $description,
                    $goal_amount,
                    $start_date,
                    $end_date,
                    $file_path
                ]);
                
                $success = "Funding request submitted successfully! Awaiting admin approval.";
            } catch (PDOException $e) {
                error_log("Database Error: " . $e->getMessage());
                $errors[] = "Failed to submit request. Please try again.";
            }
        }
    }
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Funding Request - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Create Funding Request</h2>
        
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

        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="row g-3">
                <div class="col-12">
                    <label for="request_title" class="form-label">Request Title</label>
                    <input type="text" class="form-control" id="request_title" name="request_title" required>
                </div>

                <div class="col-12">
                    <label for="request_description" class="form-label">Description</label>
                    <textarea class="form-control" id="request_description" name="request_description" 
                        rows="4" required placeholder="Describe the cause and how the funds will be used..."></textarea>
                </div>

                <div class="col-md-6">
                    <label for="goal_amount" class="form-label">Goal Amount (₹)</label>
                    <input type="number" class="form-control" id="goal_amount" name="goal_amount" 
                        min="1000" step="100" required>
                </div>

                <div class="col-md-6">
                    <label for="request_image" class="form-label">Upload Image</label>
                    <input type="file" class="form-control" id="request_image" name="request_image" 
                        accept="image/*" required>
                    <small class="text-muted">Upload an image that represents your cause</small>
                </div>

                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="col-12">
                    <button type="submit" name="create_funding_request" class="btn btn-primary">
                        Submit Funding Request
                    </button>
                    <a href="orphanage_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Date validation script
        const today = new Date().toISOString().split('T')[0];
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        startDateInput.min = today;
        endDateInput.min = today;

        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value;
            if (endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        });
    </script>
</body>
</html> 