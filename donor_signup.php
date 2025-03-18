<?php
session_start();
require_once 'connect.php';

$signup_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $aadhar = $_FILES['aadhar'] ?? '';

    // Validate inputs
    if (empty($name)) {
        $signup_errors[] = "Name is required";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signup_errors[] = "Valid email is required";
    }

    if (empty($password)) {
        $signup_errors[] = "Password is required";
    }

    if ($password !== $confirm_password) {
        $signup_errors[] = "Passwords do not match";
    }

    // Check if email already exists
    if (empty($signup_errors)) {
        try {
            $check_email_stmt = $pdo->prepare("SELECT id FROM donors WHERE email = ?");
            $check_email_stmt->execute([$email]);
            if ($check_email_stmt->fetch()) {
                $signup_errors[] = "Email already exists";
            }
        } catch (PDOException $e) {
            error_log("Email Check Error: " . $e->getMessage());
            $signup_errors[] = "Database error occurred";
        }
    }

    // Handle file upload
    if (empty($signup_errors) && isset($_FILES['aadhar'])) {
        $upload_dir = 'uploads/aadhar/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['aadhar']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $new_filename;

        if (!move_uploaded_file($_FILES['aadhar']['tmp_name'], $target_file)) {
            $signup_errors[] = "Failed to upload Aadhar document";
        }
    }

    // If no errors, create account
    if (empty($signup_errors)) {
        try {
            // First, store the file
            $upload_dir = 'uploads/aadhar/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['aadhar']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (!move_uploaded_file($_FILES['aadhar']['tmp_name'], $target_file)) {
                throw new Exception("Failed to upload Aadhar document");
            }

            // Then create the account
            $stmt = $pdo->prepare("
                INSERT INTO donors (name, email, mobile, password, aadhar, role, status)
                VALUES (?, ?, ?, ?, ?, 'donor', 'active')
            ");
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $result = $stmt->execute([
                $name,
                $email,
                $mobile,
                $hashed_password,
                $target_file
            ]);

            if ($result) {
                $_SESSION['success_message'] = "Account created successfully! Please login.";
                header("Location: login.php");
                exit();
            } else {
                throw new Exception("Failed to create account");
            }
        } catch (Exception $e) {
            error_log("Signup Error: " . $e->getMessage());
            $signup_errors[] = "Failed to create account: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CHARITEX - Donor Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-image: url("n2.jpg");
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
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.8s ease-in-out;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #1a2a6c;
            margin-bottom: 0.5rem;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .input-icon input {
            padding-left: 40px;
        }

        input, select {
            width: 100%;
            padding: 1rem 1rem 1rem 2.5rem;
            border: 2px solid #ddd;
            border-radius: 16px;
            font-size: 1rem;
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
            margin-bottom: 1rem;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #1a2a6c;
            box-shadow: 0 0 8px rgba(26, 42, 108, 0.2);
        }

        .form-btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin: 0 auto 1rem auto;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.3);
        }

        .form-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 65, 108, 0.4);
            background: linear-gradient(45deg, #ff4b2b, #ff416c);
        }

        .form-btn:active {
            transform: translateY(-1px);
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .error-message {
            color: #ff4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            padding-left: 1rem;
            display: none;
        }

        .custom-select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 16px;
            font-size: 1rem;
            color: #666;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }

        .login-text {
            color: #666;
            font-size: 0.95rem;
            margin-top: 1rem;
            text-align: center;
        }

        .login-text a {
            color: #ff416c;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-text a:hover {
            color: #ff4b2b;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }
            input, select {
                font-size: 16px;
            }
        }

        /* File input styling */
        input[type="file"] {
            padding: 0.8rem;
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 16px;
            cursor: pointer;
        }

        input[type="file"]:hover {
            border-color: #ff416c;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Join as a Donor</h1>
            <p>Support causes that matter</p>
        </div>

        <?php if (!empty($signup_errors)): ?>
            <div class="error-message" style="display: block; color: #ff4444; text-align: center; margin-bottom: 15px;">
                <?php foreach ($signup_errors as $error): ?>
                    <?php echo htmlspecialchars($error); ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form id="signupForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <input type="text" id="name" name="name" placeholder="Full name (at least 2 words)" required />
                <div class="error-message" id="name-error"></div>
            </div>

            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Enter a valid email address" required />
                <div class="error-message" id="email-error"></div>
            </div>

            <div class="form-group">
                <input type="tel" id="mobile" name="mobile" placeholder="Enter 10-digit mobile number" pattern="[0-9]{10}" required />
                <div class="error-message" id="mobile-error"></div>
            </div>

            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password (min 8 chars, 1 uppercase, 1 number)" required />
                <div class="error-message" id="password-error"></div>
            </div>

            <div class="form-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required />
                <div class="error-message" id="confirm-password-error"></div>
            </div>

            <div class="form-group">
                <label for="aadhar">Upload Aadhar Document</label>
                <input type="file" id="aadhar" name="aadhar" accept="application/pdf,image/*" required />
                <div class="error-message" id="aadhar-error"></div>
            </div>

            <div class="form-bottom">
                <button type="submit" name="submit" class="form-btn">Create Donor Account</button>
                <p class="login-text">Already have an account? <a href="login.php">Login now</a></p>
            </div>
        </form>
    </div>

    <!-- Copy all JavaScript validation code from signup.php -->
    <script>
        // Copy all JavaScript functions from signup.php
    </script>
</body>
</html>