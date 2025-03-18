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
    $location = trim($_POST['location'] ?? '');
    $availability = $_POST['availability'] ?? '';
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

    if (empty($location)) {
        $signup_errors[] = "Location is required";
    }

    if (empty($availability)) {
        $signup_errors[] = "Availability is required";
    }

    // Check if email already exists
    if (empty($signup_errors)) {
        try {
            $check_email_stmt = $pdo->prepare("SELECT id FROM volunteers WHERE email = ?");
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
            $stmt = $pdo->prepare(" INSERT INTO volunteers (
                    name, 
                    email, 
                    mobile, 
                    password, 
                    location, 
                    availability, 
                    aadhar, 
                    role, 
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'volunteer', 'pending')
            ");
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $result = $stmt->execute([
                $name,
                $email,
                $mobile,
                $hashed_password,
                $location,
                $availability,
                $target_file
            ]);

            if ($result) {
                $_SESSION['success_message'] = "Account created successfully! Please wait for admin approval before logging in.";
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
    <title>CHARITEX - Volunteer Sign Up</title>
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

        /* Additional styles for volunteer-specific fields */
        .custom-select {
            padding-left: 1rem;
        }

        .form-group select.custom-select {
            background-color: white;
            margin-bottom: 0;
        }

        .form-group input[type="text"]#location {
            padding-left: 1rem;
        }

        /* Animation styles */
        .input-focus {
            transform: translateY(-2px);
            transition: transform 0.3s ease;
        }

        .form-group input.valid,
        .form-group select.valid {
            border-color: #2ecc71;
        }

        .form-group input.error,
        .form-group select.error {
            border-color: #ff4444;
        }

        .error-message {
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .error-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Input icon animations */
        .input-icon i {
            transition: color 0.3s ease;
        }

        .form-group.valid .input-icon i {
            color: #2ecc71;
        }

        .form-group.error .input-icon i {
            color: #ff4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Join as a Volunteer</h1>
            <p>Offer your time and skills</p>
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
                <input type="text" id="location" name="location" placeholder="Enter your city or region" required />
                <div class="error-message" id="location-error"></div>
            </div>

            <div class="form-group">
                <select id="availability" name="availability" class="custom-select" required>
                    <option value="" disabled selected>Select your availability</option>
                    <option value="weekdays">Weekdays</option>
                    <option value="weekends">Weekends</option>
                    <option value="flexible">Flexible</option>
                </select>
                <div class="error-message" id="availability-error"></div>
            </div>

            <div class="form-group">
                <label for="aadhar">Upload Aadhar Document</label>
                <input type="file" id="aadhar" name="aadhar" accept="application/pdf,image/*" required />
                <div class="error-message" id="aadhar-error"></div>
            </div>

            <div class="form-bottom">
                <button type="submit" name="submit" class="form-btn">Create Volunteer Account</button>
                <p class="login-text">Already have an account? <a href="login.php">Login now</a></p>
            </div>
        </form>
    </div>

    <!-- Copy all JavaScript validation code from signup.php -->
    <script>
        function validateFullName(input) {
            const value = input.value.trim();
            const nameRegex = /^[A-Za-z]+\s+[A-Za-z\s]+$/;
            
            if (!nameRegex.test(value)) {
                input.classList.add("error");
                input.classList.remove("valid");
                
                if (!/^[A-Za-z\s]+$/.test(value)) {
                    showValidation(input, "Name should only contain letters and spaces");
                } else if (!value.includes(' ')) {
                    showValidation(input, "Please include a space after your first name");
                } else {
                    showValidation(input, "Please enter a valid full name");
                }
                return false;
            }

            const nameParts = value.split(/\s+/).filter(part => part.length > 0);
            if (nameParts[0].length < 2 || nameParts[1].length < 2) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Each name should be at least 2 characters long");
                return false;
            }

            input.classList.remove("error");
            input.classList.add("valid");
            hideValidation(input);
            return true;
        }

        function validateEmail(input) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const value = input.value.trim();

            if (!emailRegex.test(value)) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Please enter a valid email address");
                return false;
            }

            input.classList.remove("error");
            input.classList.add("valid");
            hideValidation(input);
            return true;
        }

        function validatePassword(input) {
            const value = input.value;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;

            if (!passwordRegex.test(value)) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Password must be at least 8 characters with 1 uppercase, 1 lowercase, and 1 number");
                return false;
            }

            input.classList.remove("error");
            input.classList.add("valid");
            hideValidation(input);
            return true;
        }

        function validateConfirmPassword(input) {
            const password = document.getElementById("password").value;
            const confirmPassword = input.value;

            if (password !== confirmPassword) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Passwords do not match");
                return false;
            }

            input.classList.remove("error");
            input.classList.add("valid");
            hideValidation(input);
            return true;
        }

        function validateMobile(input) {
            const value = input.value.trim();
            
            if (!value) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Mobile number is required");
                return false;
            }
            
            if (!/^\d+$/.test(value)) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Please enter only numbers");
                return false;
            }
            
            if (value.startsWith('4')) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Mobile number cannot start with 4");
                return false;
            }
            
            if (value.length !== 10) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Mobile number must be exactly 10 digits");
                return false;
            }

            input.classList.remove("error");
            input.classList.add("valid");
            hideValidation(input);
            return true;
        }

        function validateAadhar(input) {
            const file = input.files[0];
            if (!file) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Please select an Aadhar document");
                return false;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
            if (!validTypes.includes(file.type)) {
                input.classList.add("error");
                input.classList.remove("valid");
                showValidation(input, "Please upload a valid image or PDF file");
                return false;
            }

            input.classList.remove("error");
            input.classList.add("valid");
            hideValidation(input);
            return true;
        }

        function showValidation(input, message) {
            const errorElement = document.getElementById(`${input.id}-error`);
            errorElement.textContent = message;
            errorElement.style.display = "block";
            input.classList.add("error");
        }

        function hideValidation(input) {
            const errorElement = document.getElementById(`${input.id}-error`);
            errorElement.style.display = "none";
            input.classList.remove("error");
        }

        // Form validation on submit
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            if (!validateFullName(document.getElementById('name'))) isValid = false;
            if (!validateEmail(document.getElementById('email'))) isValid = false;
            if (!validateMobile(document.getElementById('mobile'))) isValid = false;
            if (!validatePassword(document.getElementById('password'))) isValid = false;
            if (!validateConfirmPassword(document.getElementById('confirm_password'))) isValid = false;
            if (!validateAadhar(document.getElementById('aadhar'))) isValid = false;

            // Additional validation for volunteer form
            if (document.getElementById('location')) {
                const location = document.getElementById('location');
                if (!location.value.trim()) {
                    showValidation(location, "Location is required");
                    isValid = false;
                }
            }

            if (document.getElementById('availability')) {
                const availability = document.getElementById('availability');
                if (!availability.value) {
                    showValidation(availability, "Please select your availability");
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Live validation
        document.getElementById('name').addEventListener('input', function() {
            validateFullName(this);
        });

        document.getElementById('email').addEventListener('input', function() {
            validateEmail(this);
        });

        document.getElementById('mobile').addEventListener('input', function() {
            validateMobile(this);
        });

        document.getElementById('password').addEventListener('input', function() {
            validatePassword(this);
            if (document.getElementById('confirm_password').value) {
                validateConfirmPassword(document.getElementById('confirm_password'));
            }
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            validateConfirmPassword(this);
        });

        document.getElementById('aadhar').addEventListener('change', function() {
            validateAadhar(this);
        });

        // Additional event listeners for volunteer form
        if (document.getElementById('location')) {
            document.getElementById('location').addEventListener('input', function() {
                if (!this.value.trim()) {
                    showValidation(this, "Location is required");
                } else {
                    hideValidation(this);
                }
            });
        }

        if (document.getElementById('availability')) {
            document.getElementById('availability').addEventListener('change', function() {
                if (!this.value) {
                    showValidation(this, "Please select your availability");
                } else {
                    hideValidation(this);
                }
            });
        }

        // Add animation class on focus
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('focus', function() {
                this.classList.add('input-focus');
            });

            element.addEventListener('blur', function() {
                this.classList.remove('input-focus');
            });
        });
    </script>
</body>
</html>