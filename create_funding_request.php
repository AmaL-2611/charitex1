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
    
    <!-- Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            --shadow: rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f6f8fb 0%, #f1f4f9 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .form-card {
            background: var(--surface);
            border-radius: 15px;
            box-shadow: 0 10px 30px var(--shadow);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }

        .form-card-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .form-card-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid var(--border);
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: var(--primary);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .action-buttons {
            display: inline-flex;
            gap: 15px;
            align-items: center;
            justify-content: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn i {
            font-size: 0.9rem;
        }

        .file-preview {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 1rem;
            box-shadow: 0 4px 12px var(--shadow);
        }

        .file-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <div class="form-card-header">
                        <h2><i class="fas fa-hand-holding-heart"></i> Create Funding Request</h2>
                        <p class="mb-0">Please fill in the funding request details</p>
                    </div>

                    <div class="form-card-body">
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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label">
                                        <i class="fas fa-heading"></i> Title
                                    </label>
                                    <input type="text" class="form-control" id="title" name="request_title" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="required_amount" class="form-label">
                                        <i class="fas fa-money-bill"></i> Required Amount
                                    </label>
                                    <input type="number" class="form-control" id="required_amount" name="goal_amount" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <textarea class="form-control" id="description" name="request_description" rows="4" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">
                                    <i class="fas fa-image"></i> Upload Image
                                </label>
                                <input type="file" class="form-control" id="image" name="request_image" accept="image/*" required>
                                <div class="file-preview mt-2" style="display: none;">
                                    <img id="imagePreview" src="#" alt="Preview">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">
                                        <i class="fas fa-calendar-alt"></i> Start Date
                                    </label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">
                                        <i class="fas fa-calendar"></i> End Date
                                    </label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" name="create_funding_request" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <div class="action-buttons">
                        <a href="active_funding_requests.php" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> View Funding Requests
                        </a>
                        <a href="orphanage_dashboard.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.querySelector('.file-preview');
            const image = document.getElementById('imagePreview');
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    image.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Disable previous dates
        const dateInputs = document.querySelectorAll('input[type="date"]');
        const today = new Date().toISOString().split('T')[0];

        dateInputs.forEach(input => {
            input.setAttribute('min', today);
        });
    </script>
</body>
</html> 