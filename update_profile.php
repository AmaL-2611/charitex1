<?php
session_start();

// Validate session before proceeding
if (!isset($_SESSION['user_type']) || !isset($_SESSION['user_id'])) {
    // Redirect to login page if session is invalid
    header("Location: login.php");
    exit();
}

require_once 'connect.php';

// Determine the correct table based on user type with additional safety checks
$table = ($_SESSION['user_type'] === 'donor') ? 'donors' : 
         (($_SESSION['user_type'] === 'volunteer') ? 'volunteers' : '');

if (empty($table)) {
    // Invalid user type
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

try {
    // Get current user data with prepared statement
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Additional check if user not found
    if (!$user) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    // Log error and redirect
    error_log("Database error: " . $e->getMessage());
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize error and success arrays
    $errors = [];
    $success = '';

    // Profile Image Upload
    $relative_path = ''; // Initialize relative path variable
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        // Validate file type
        $allowed_types = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif']
        ];
        $max_size = 5 * 1024 * 1024; // 5MB
        $upload_dir = 'uploads/profile/';

        // Get file details
        $file_type = $_FILES['profile_photo']['type'];
        $file_size = $_FILES['profile_photo']['size'];
        $file_tmp = $_FILES['profile_photo']['tmp_name'];
        $file_name = $_FILES['profile_photo']['name'];

        // Validate file type and size
        if (!array_key_exists($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Only JPEG, PNG, and GIF are allowed.";
        } elseif ($file_size > $max_size) {
            $errors[] = "File size exceeds 5MB limit.";
        } else {
            // Additional security checks
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_types[$file_type])) {
                $errors[] = "File extension does not match file type.";
            }

            // Check if file is actually an image
            $image_info = getimagesize($file_tmp);
            if (!$image_info) {
                $errors[] = "Invalid image file.";
            }
        }

        // If no errors, process the upload
        if (empty($errors)) {
            // Create uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate unique filename
            $new_filename = $table . '_profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            $relative_path = $upload_path; // Store relative path in database and session

            // Move uploaded file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                try {
                    // Update profile photo in database
                    $update_photo_stmt = $pdo->prepare("UPDATE $table SET profile = ? WHERE id = ?");
                    $update_photo_stmt->execute([$relative_path, $_SESSION['user_id']]);
                    
                    $success = "Profile photo updated successfully!";
                } catch (PDOException $e) {
                    $errors[] = "Database error updating profile photo: " . $e->getMessage();
                    // Remove the uploaded file if database update fails
                    unlink($upload_path);
                    $relative_path = ''; // Reset relative path
                }
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        }
    } elseif (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other file upload errors
        switch ($_FILES['profile_photo']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = "File is too large.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = "File was only partially uploaded.";
                break;
            default:
                $errors[] = "An unknown error occurred during file upload.";
        }
    }

    // Rest of the existing profile update logic...
    $name = trim($_POST['name']);
    $mobile = trim($_POST['mobile']);
    $new_password = trim($_POST['new_password']);
    $userId = $_SESSION['user_id'];
    
    // Check if mobile number already exists for other users
    $check_mobile = $pdo->prepare("SELECT id FROM $table WHERE mobile = ? AND id != ?");
    $check_mobile->execute([$mobile, $userId]);
    
    if ($check_mobile->rowCount() > 0) {
        $errors[] = "Mobile number already exists!";
    } else {
        // Build update query based on whether password is being updated
        if (!empty($new_password)) {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE $table SET name = ?, mobile = ?, password = ? WHERE id = ?";
            $params = [$name, $mobile, $hashedPassword, $userId];
        } else {
            $sql = "UPDATE $table SET name = ?, mobile = ? WHERE id = ?";
            $params = [$name, $mobile, $userId];
        }
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Update session variables
            $_SESSION['name'] = $name;
            
            // If a profile image was uploaded, update session
            if (!empty($relative_path)) {
                $_SESSION['profile_image'] = $relative_path;
            }
            
            // Redirect based on user type
            if ($table === 'donors') {
                header("Location: donor.php");
            } elseif ($table === 'volunteers') {
                header("Location: volunteer.php");
            } else {
                header("Location: login.php");
            }
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error updating profile: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - CHARITEX</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", "Arial", sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-image: url("https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?ixlib=rb-4.0.3");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                rgba(26, 42, 108, 0.8),
                rgba(178, 31, 31, 0.8)
            );
            z-index: -1;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            backdrop-filter: blur(10px);
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header h1 {
            color: #1a2a6c;
            margin-bottom: 0.8rem;
            font-size: 2.2rem;
            font-weight: 600;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 0.7rem;
            color: #1a2a6c;
            font-weight: 500;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        input:focus {
            outline: none;
            border-color: #1a2a6c;
            box-shadow: 0 0 0 3px rgba(26, 42, 108, 0.1);
        }

        input.error {
            border-color: #ff4444;
            background-color: rgba(255, 68, 68, 0.05);
        }

        input.valid {
            border-color: #00c851;
            background-color: rgba(0, 200, 81, 0.05);
        }

        .error-message {
            color: #ff4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }

        .password-hint {
            color: #666;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1a2a6c, #2a3a7c);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2a3a7c, #3a4a8c);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 42, 108, 0.2);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #1a2a6c;
            border: 2px solid #e1e1e1;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Update Profile</h1>
            <p>Edit your information below</p>
        </div>

        <form method="POST" onsubmit="return validateForm()" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                       required>
                <div id="name-error" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="mobile">Mobile Number</label>
                <input type="tel" id="mobile" name="mobile" 
                       value="<?php echo htmlspecialchars($user['mobile'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                       required pattern="[0-9]{10}" title="Please enter a 10-digit mobile number">
                <div id="mobile-error" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="new_password">New Password (Optional)</label>
                <input type="password" id="new_password" name="new_password" 
                       placeholder="Leave blank if you don't want to change password">
                <div class="password-hint">Leave blank if you don't want to change password</div>
                <div id="password-error" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="profile_photo">Profile Photo</label>
                <input type="file" id="profile_photo" name="profile_photo" accept=".jpg, .jpeg, .png, .gif">
                <div id="profile-photo-error" class="error-message"></div>
            </div>

            <div class="buttons">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="donor.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function validateMobile(input) {
            const value = input.value.trim();
            const errorElement = document.getElementById('mobile-error');
            
            if (!value) {
                showError(input, errorElement, "Mobile number is required");
                return false;
            }
            
            if (!/^[5-9]\d{9}$/.test(value)) {
                showError(input, errorElement, "Enter valid 10-digit number (can't start with 4)");
                return false;
            }
            
            hideError(input, errorElement);
            return true;
        }

        function validateName(input) {
            const value = input.value.trim();
            const errorElement = document.getElementById('name-error');
            
            if (!value) {
                showError(input, errorElement, "Name is required");
                return false;
            }
            
            if (!/^[A-Za-z]+\s+[A-Za-z\s]+$/.test(value)) {
                showError(input, errorElement, "Please enter your full name (first and last name)");
                return false;
            }
            
            hideError(input, errorElement);
            return true;
        }

        function validatePassword(input) {
            const value = input.value.trim();
            const errorElement = document.getElementById('password-error');
            
            if (value && value.length < 8) {
                showError(input, errorElement, "Password must be at least 8 characters");
                return false;
            }
            
            if (value && !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/.test(value)) {
                showError(input, errorElement, "Password must contain at least one uppercase letter, one lowercase letter, and one number");
                return false;
            }
            
            hideError(input, errorElement);
            return true;
        }

        function validateProfilePhoto(input) {
            const value = input.value.trim();
            const errorElement = document.getElementById('profile-photo-error');
            
            if (!value) {
                hideError(input, errorElement);
                return true;
            }
            
            const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            const fileExtension = value.split('.').pop().toLowerCase();
            
            if (!allowedExtensions.includes(fileExtension)) {
                showError(input, errorElement, "Only JPEG, PNG, and GIF files are allowed");
                return false;
            }
            
            hideError(input, errorElement);
            return true;
        }

        function showError(input, errorElement, message) {
            input.classList.add('error');
            input.classList.remove('valid');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        function hideError(input, errorElement) {
            input.classList.remove('error');
            input.classList.add('valid');
            errorElement.style.display = 'none';
        }

        function validateForm() {
            const nameValid = validateName(document.getElementById("name"));
            const mobileValid = validateMobile(document.getElementById("mobile"));
            const passwordValid = validatePassword(document.getElementById("new_password"));
            const profilePhotoValid = validateProfilePhoto(document.getElementById("profile_photo"));
            return nameValid && mobileValid && passwordValid && profilePhotoValid;
        }

        // Live validation
        document.getElementById("mobile").addEventListener("input", function() {
            validateMobile(this);
        });

        document.getElementById("name").addEventListener("input", function() {
            validateName(this);
        });

        document.getElementById("new_password").addEventListener("input", function() {
            validatePassword(this);
        });

        document.getElementById("profile_photo").addEventListener("input", function() {
            validateProfilePhoto(this);
        });
    </script>
</body>
</html>