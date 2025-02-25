<?php
session_start();
require_once 'config.php'; // Create this file with database connection details

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Admin login check
        if ($userType === 'admin') 
        {
            // Hardcoded admin credentials
            $adminEmail = "admin@charitex.com";
            $adminPassword = "admin123"; // In real-world, use hashed password

            if ($email === $adminEmail && $password === $adminPassword) {
                // Admin login successful
                $_SESSION['user_id'] = 1;
                $_SESSION['user_type'] = 'admin';
                $_SESSION['email'] = $adminEmail;
                $_SESSION['name'] = 'Admin';
                
                header("Location: admin_dashboard.php");
                exit();
            }
        } else {
            // Regular user login
            $table = ($userType === 'donor') ? 'donors' : 'volunteers';
            
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] === 'inactive') {
                    header("Location: login.php?error=Your account has been deactivated. Please contact the administrator.");
                    exit();
                }
                
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $userType;
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                
                // Redirect to appropriate dashboard based on session
                if ($userType === 'donor') {
                    $_SESSION['logged_in'] = true;
                    header("Location: donor.php");
                } else {
                    header("Location: volunteer.php");
                }
                exit();
            }
        }
        
        // Login failed
        header("Location: login.php?error=Invalid email or password");
        exit();
    } catch (PDOException $e) {
        header("Location: login.php?error=An error occurred. Please try again later.");
        exit();
    }
}  ?>
