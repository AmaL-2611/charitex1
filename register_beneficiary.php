<?php
session_start();
require_once 'config.php';

// Check if orphanage is logged in
if (!isset($_SESSION['orphanage_id'])) {
    header('location:login_orphanage.php');
    exit();
}

// Initialize variables
$errors = [];
$success = '';
$support_type = ''; // Initialize support_type variable

// Modify the beneficiaries table creation
include 'connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize support_type
    $support_type = trim($_POST['support_type'] ?? '');

    // Validate and sanitize inputs
    $full_name = trim($_POST['full_name'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    // Validation
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($date_of_birth)) $errors[] = "Date of birth is required";
    if (empty($gender)) $errors[] = "Gender is required";

    // Validate amount
    $required_amount = filter_input(INPUT_POST, 'required_amount', FILTER_VALIDATE_FLOAT);
    if (!$required_amount || $required_amount < 1000) {
        $errors[] = "Please enter a valid amount (minimum ₹1,000)";
    }

    // Handle file uploads
    $file_paths = [];
    if ($support_type === 'Education') {
        $file_fields = [
            'admission_letter' => 'admission_letter_path',
            'fee_structure' => 'fee_structure_path',
            'report_card' => 'report_card_path',
            'recommendation_letter' => 'recommendation_letter_path',
            'guardian_id' => 'guardian_id_path'
        ];

        foreach ($file_fields as $field => $path) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === 0) {
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                $filename = $_FILES[$field]['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    $errors[] = "Please upload valid file formats (PDF, JPG, JPEG, PNG) for " . str_replace('_', ' ', $field);
                } else {
                    $upload_dir = 'uploads/beneficiary_documents/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $file_paths[$path] = $upload_dir . uniqid() . '_' . $field . '.' . $ext;
                    move_uploaded_file($_FILES[$field]['tmp_name'], $file_paths[$path]);
                }
            }
        }
    }

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $errors[] = "Please upload valid image formats (JPG, PNG) for profile image";
        } else {
            $upload_dir = 'uploads/beneficiary_images/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $profile_image_path = $upload_dir . uniqid() . '_profile.' . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $profile_image_path);
        }
    } else {
        $errors[] = "Profile image is required";
    }

    // Handle medical document uploads
    if ($support_type === 'Medical') {
        $medical_files = [
            'aadhaar' => 'aadhaar_path',
            'prescription' => 'prescription_path',
            'hospital_letter' => 'hospital_letter_path',
            'cost_estimate' => 'cost_estimate_path',
            'surgery_letter' => 'surgery_letter_path',
            'insurance_letter' => 'insurance_letter_path'
        ];

        foreach ($medical_files as $field => $path) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === 0) {
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                $filename = $_FILES[$field]['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    $errors[] = "Please upload valid file formats for " . str_replace('_', ' ', $field);
                } else {
                    $upload_dir = 'uploads/medical_documents/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $file_paths[$path] = $upload_dir . uniqid() . '_' . $field . '.' . $ext;
                    move_uploaded_file($_FILES[$field]['tmp_name'], $file_paths[$path]);
                }
            }
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO beneficiaries (
                orphanage_id, 
                full_name, 
                date_of_birth, 
                gender, 
                support_type,
                profile_image,
                admission_letter_path, 
                fee_structure_path, 
                report_card_path,
                aadhaar_path,
                prescription_path,
                hospital_letter_path,
                cost_estimate_path,
                surgery_letter_path,
                insurance_letter_path,
                required_amount,
                status
            ) VALUES (
                :orphanage_id,
                :full_name,
                :date_of_birth,
                :gender,
                :support_type,
                :profile_image,
                :admission_letter_path,
                :fee_structure_path,
                :report_card_path,
                :aadhaar_path,
                :prescription_path,
                :hospital_letter_path,
                :cost_estimate_path,
                :surgery_letter_path,
                :insurance_letter_path,
                :required_amount,
                'Active'
            )";

            $stmt = $pdo->prepare($sql);
            
            $params = [
                ':orphanage_id' => $_SESSION['orphanage_id'],
                ':full_name' => $full_name,
                ':date_of_birth' => $date_of_birth,
                ':gender' => $gender,
                ':support_type' => $support_type,
                ':profile_image' => $profile_image_path ?? null,
                ':admission_letter_path' => $file_paths['admission_letter_path'] ?? null,
                ':fee_structure_path' => $file_paths['fee_structure_path'] ?? null,
                ':report_card_path' => $file_paths['report_card_path'] ?? null,
                ':aadhaar_path' => $file_paths['aadhaar_path'] ?? null,
                ':prescription_path' => $file_paths['prescription_path'] ?? null,
                ':hospital_letter_path' => $file_paths['hospital_letter_path'] ?? null,
                ':cost_estimate_path' => $file_paths['cost_estimate_path'] ?? null,
                ':surgery_letter_path' => $file_paths['surgery_letter_path'] ?? null,
                ':insurance_letter_path' => $file_paths['insurance_letter_path'] ?? null,
                ':required_amount' => $required_amount
            ];

            $result = $stmt->execute($params);

            if ($result) {
                $success = "Beneficiary registered successfully!";
                $_POST = array();
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Beneficiary - CHARITEX</title>
    
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
            --shadow: rgba(0, 0, 0, 0.1);
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

        .education-fields, .medical-fields {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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

        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .success-modal .modal-content {
            border-radius: 15px;
        }

        .success-icon {
            font-size: 4rem;
            color: var(--secondary);
            margin: 1rem 0;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn i {
            font-size: 0.9rem;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .view-requests-button {
            text-align: right;
            padding: 1rem 0;
        }

        .view-requests-button .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .view-requests-button .btn:hover {
            transform: translateY(-2px);
            background-color: var(--primary);
            color: white;
        }

        .view-requests-button .btn i {
            font-size: 0.85rem;
        }

        .submit-btn {
            padding: 0.75rem 2rem;
            font-weight: 500;
            font-size: 1rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            min-width: 200px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }

        .view-requests-btn {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .view-requests-btn:hover {
            transform: translateY(-2px);
            background-color: var(--primary);
            color: white;
        }

        .action-buttons {
            display: inline-flex;
            gap: 15px;
            align-items: center;
            justify-content: center;
        }

        .back-btn {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .back-btn:hover {
            transform: translateX(-3px);
        }

        @media (max-width: 576px) {
            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .view-requests-btn, .back-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <div class="form-card-header">
                        <h2><i class="fas fa-user-plus"></i> Register Beneficiary</h2>
                        <p class="mb-0">Please fill in the beneficiary details</p>
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
                                    <label for="full_name" class="form-label">
                                        <i class="fas fa-user"></i> Full Name
                                    </label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">
                                        <i class="fas fa-calendar"></i> Date of Birth
                                    </label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-venus-mars"></i> Gender
                                    </label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="profile_image" class="form-label">
                                        <i class="fas fa-camera"></i> Profile Image
                                    </label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="profile_image" 
                                           name="profile_image" 
                                           accept=".jpg,.jpeg,.png" 
                                           required>
                                    <small class="text-muted">Upload a clear photo (JPG, PNG only)</small>
                                    
                                    <!-- Image Preview -->
                                    <div class="file-preview mt-2" style="display: none;">
                                        <img id="imagePreview" src="#" alt="Profile Preview" 
                                             class="img-fluid rounded" style="max-width: 150px;">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="support_type" class="form-label">
                                        <i class="fas fa-hands-helping"></i> Support Type
                                    </label>
                                    <select class="form-select" id="support_type" name="support_type" required onchange="toggleSupportFields()">
                                        <option value="">Select Support Type</option>
                                        <option value="Education">Education Support</option>
                                        <option value="Medical">Medical Support</option>
                                    </select>
                                </div>
                            </div>

                            <div id="education-fields" class="education-fields">
                                <h4 class="mt-4">Education Support Documents</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="admission_letter" class="form-label">Admission Letter / Enrollment Proof</label>
                                        <input type="file" class="form-control" id="admission_letter" name="admission_letter">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="fee_structure" class="form-label">Fee Structure</label>
                                        <input type="file" class="form-control" id="fee_structure" name="fee_structure">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="report_card" class="form-label">Last Academic Report Card</label>
                                        <input type="file" class="form-control" id="report_card" name="report_card">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="recommendation_letter" class="form-label">Recommendation Letter</label>
                                        <input type="file" class="form-control" id="recommendation_letter" name="recommendation_letter">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="guardian_id" class="form-label">Guardian's/Staff ID Proof</label>
                                        <input type="file" class="form-control" id="guardian_id" name="guardian_id">
                                    </div>
                                </div>
                            </div>

                            <div id="medicalFields" class="medical-fields">
                                <div class="mb-3">
                                    <label for="aadhaar" class="form-label">Aadhaar Card / Government ID</label>
                                    <input type="file" class="form-control" id="aadhaar" name="aadhaar" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Upload government ID proof</small>
                                </div>

                                <div class="mb-3">
                                    <label for="prescription" class="form-label">Doctor's Prescription & Diagnosis Report</label>
                                    <input type="file" class="form-control" id="prescription" name="prescription" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Upload detailed medical diagnosis</small>
                                </div>

                                <div class="mb-3">
                                    <label for="hospital_letter" class="form-label">Hospital Admission Letter</label>
                                    <input type="file" class="form-control" id="hospital_letter" name="hospital_letter" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">If admitted, upload hospital letter</small>
                                </div>

                                <div class="mb-3">
                                    <label for="cost_estimate" class="form-label">Treatment Cost Estimate</label>
                                    <input type="file" class="form-control" id="cost_estimate" name="cost_estimate" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Upload detailed cost breakdown</small>
                                </div>

                                <div class="mb-3">
                                    <label for="surgery_letter" class="form-label">Surgery/Procedure Recommendation</label>
                                    <input type="file" class="form-control" id="surgery_letter" name="surgery_letter" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Upload surgery recommendation letter</small>
                                </div>

                                <!-- <div class="mb-3">
                                    <label for="insurance_letter" class="form-label">Medical Insurance Claim Letter</label>
                                    <input type="file" class="form-control" id="insurance_letter" name="insurance_letter" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">If applicable, upload insurance claim details</small>
                                </div> -->
                            </div>

                            <div class="mb-3">
                                <label for="required_amount" class="form-label">Required Amount (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="required_amount" 
                                           name="required_amount" 
                                           min="1000" 
                                           step="100" 
                                           required
                                           placeholder="Enter the required amount">
                                </div>
                                <small class="text-muted">
                                    <?php if ($support_type === 'Education'): ?>
                                        Please enter the total education expenses including tuition, books, and supplies
                                    <?php else: ?>
                                        Please enter the total medical expenses as per the hospital estimate
                                    <?php endif; ?>
                                </small>
                            </div>

                            <div class="text-center mt-4 mb-3">
                                <button type="submit" class="btn btn-primary submit-btn">
                                    <i class="fas fa-save"></i> Register Beneficiary
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <div class="action-buttons">
                <a href="requested_beneficiaries.php" class="btn btn-outline-primary view-requests-btn">
                    <i class="fas fa-list"></i> View Requested Beneficiaries
                </a>
                <a href="orphanage_dashboard.php" class="btn btn-outline-primary back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade success-modal" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Success!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-check-circle success-icon"></i>
                    <h4>Beneficiary Registered Successfully</h4>
                    <p>The beneficiary has been added to your dashboard.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="orphanage_dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSupportFields() {
            const supportType = document.getElementById('support_type').value;
            const educationFields = document.getElementById('education-fields');
            const medicalFields = document.getElementById('medicalFields');

            if (supportType === 'Education') {
                educationFields.style.display = 'block';
                medicalFields.style.display = 'none';
            } else if (supportType === 'Medical') {
                educationFields.style.display = 'none';
                medicalFields.style.display = 'block';
            } else {
                educationFields.style.display = 'none';
                medicalFields.style.display = 'none';
            }
        }

        // Show Success Modal
        <?php if ($success): ?>
            new bootstrap.Modal(document.getElementById('successModal')).show();
        <?php endif; ?>

        // Add this JavaScript for image preview
        document.getElementById('profile_image').addEventListener('change', function(e) {
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
    </script>
</body>
</html>
