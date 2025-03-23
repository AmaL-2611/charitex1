<?php
// Start the session and include database connection
session_start();
include 'config.php';

// Check if connection is established
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if(isset($_POST['submit'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, md5($_POST['password']));
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $reg_number = mysqli_real_escape_string($conn, $_POST['reg_number']);

    $select = mysqli_query($conn, "SELECT * FROM `orphanage` WHERE email = '$email'") or die('query failed');

    if(mysqli_num_rows($select) > 0){
        $message[] = 'Orphanage already registered!';
    }else{
        mysqli_query($conn, "INSERT INTO `orphanage`(name, email, password, location, contact, reg_number) VALUES('$name', '$email', '$password', '$location', '$contact', '$reg_number')") or die('query failed');
        $message[] = 'Registration successful!';
        header('location:login_orphanage.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHARITEX - Orphanage Registration</title>
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
            background-image: url("n1.jpg");
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

        input {
            width: 100%;
            padding: 1rem 1rem 1rem 2.5rem;
            border: 2px solid #ddd;
            border-radius: 16px;
            font-size: 1rem;
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
            margin-bottom: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #1a2a6c;
            box-shadow: 0 0 8px rgba(26, 42, 108, 0.2);
        }

        .form-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1a2a6c, #4a4eff);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(26, 42, 108, 0.3);
            transition: all 0.3s ease;
        }

        .form-btn:hover {
            background: linear-gradient(135deg, #4a4eff, #1a2a6c);
            transform: scale(1.05);
        }

        .form-btn:active {
            transform: scale(0.98);
        }

        p a {
            position: relative;
            color: #1a2a6c;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        p a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 100%;
            height: 2px;
            background-color: #1a2a6c;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        p a:hover::after {
            transform: scaleX(1);
        }

        .help-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #ff5c5c;
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .help-btn:hover {
            background: #e84c4c;
            transform: scale(1.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Keep existing styles for error messages and other functionality */
        .error-message {
            color: #ff4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }

        .message {
            text-align: center;
            margin-bottom: 1.5rem;
            background-color: rgba(255, 68, 68, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Register Your Orphanage</h1>
            <p>Join our community to make a difference</p>
        </div>

        <form action="" method="post" id="registrationForm">
            <?php
            if(isset($message)){
                foreach($message as $message){
                    echo '<div class="message">'.$message.'</div>';
                }
            }
            ?>
            <div class="form-group">
                <label for="name">Orphanage Name</label>
                <div class="input-icon">
                    <i class="fas fa-building"></i>
                    <input type="text" name="name" id="name" required>
                </div>
                <span id="name-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="email" required>
                </div>
                <span id="email-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" required>
                </div>
                <span id="password-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <div class="input-icon">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" name="location" id="location" required>
                </div>
                <span id="location-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="contact">Contact Number</label>
                <div class="input-icon">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="contact" id="contact" required>
                </div>
                <span id="contact-error" class="error-message"></span>
            </div>

            <div class="form-group">
                <label for="reg_number">Registration Number</label>
                <div class="input-icon">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="reg_number" id="reg_number" required>
                </div>
                <span id="reg-error" class="error-message"></span>
            </div>

            <input type="submit" name="submit" value="Register Now" class="form-btn">
            <p>Already have an account? <a href="login_orphanage.php">Login now</a></p>
        </form>
    </div>

    <button class="help-btn" title="Need Help?">
        <i class="fas fa-question"></i>
    </button>

    <!-- Keep existing JavaScript validation code -->
    <script>
        // Existing validation code remains unchanged
        document.getElementById('name').addEventListener('input', validateName);
        document.getElementById('email').addEventListener('input', validateEmail);
        document.getElementById('password').addEventListener('input', validatePassword);
        document.getElementById('location').addEventListener('input', validateLocation);
        document.getElementById('contact').addEventListener('input', validateContact);
        document.getElementById('reg_number').addEventListener('input', validateRegNumber);

        // Add help button functionality
        document.querySelector('.help-btn').addEventListener('click', function() {
            alert('Need help? Contact our support team at support@charitex.com');
        });

        // Keep all existing validation functions
        function validateName() {
            const name = document.getElementById('name').value;
            const nameError = document.getElementById('name-error');
            if(name.length < 3) {
                nameError.style.display = 'block';
                nameError.textContent = 'Name must be at least 3 characters long';
                return false;
            }
            nameError.style.display = 'none';
            return true;
        }

        function validateEmail() {
            const email = document.getElementById('email').value;
            const emailError = document.getElementById('email-error');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if(!emailPattern.test(email)) {
                emailError.style.display = 'block';
                emailError.textContent = 'Please enter a valid email address';
                return false;
            }
            emailError.style.display = 'none';
            return true;
        }

        function validatePassword() {
            const password = document.getElementById('password').value;
            const passwordError = document.getElementById('password-error');
            
            if(password.length < 6) {
                passwordError.style.display = 'block';
                passwordError.textContent = 'Password must be at least 6 characters long';
                return false;
            }
            passwordError.style.display = 'none';
            return true;
        }

        function validateLocation() {
            const location = document.getElementById('location').value;
            const locationError = document.getElementById('location-error');
            
            if(location.length < 5) {
                locationError.style.display = 'block';
                locationError.textContent = 'Please enter a valid location';
                return false;
            }
            locationError.style.display = 'none';
            return true;
        }

        function validateContact() {
            const contact = document.getElementById('contact').value;
            const contactError = document.getElementById('contact-error');
            const contactPattern = /^\d{10}$/;
            
            if(!contactPattern.test(contact)) {
                contactError.style.display = 'block';
                contactError.textContent = 'Please enter a valid 10-digit contact number';
                return false;
            }
            contactError.style.display = 'none';
            return true;
        }

        function validateRegNumber() {
            const regNumber = document.getElementById('reg_number').value;
            const regError = document.getElementById('reg-error');
            
            if(regNumber.length < 5) {
                regError.style.display = 'block';
                regError.textContent = 'Please enter a valid registration number';
                return false;
            }
            regError.style.display = 'none';
            return true;
        }

        // Form submission validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            if (!validateName() || !validateEmail() || !validatePassword() || 
                !validateLocation() || !validateContact() || !validateRegNumber()) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html> 