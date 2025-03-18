<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    try {
        // Create PDO connection
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Admin login check
        if ($userType === 'admin') {
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
            } else {
                header("Location: login.php?error=Invalid admin credentials");
                exit();
            }
        } 
        // Donor or Volunteer login
        else {
            $table = ($userType === 'donor') ? 'donors' : 'volunteers';
            
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Check if account is active
                if (isset($user['status']) && $user['status'] === 'inactive') {
                    header("Location: login.php?error=Your account has been deactivated. Please contact the administrator.");
                    exit();
                }
                
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $userType;
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['logged_in'] = true;
                
                // Redirect based on user type
                if ($userType === 'donor') {
                    header("Location: donor.php");
                } else {
                    header("Location: volunteer.php");
                }
                exit();
            } else {
                header("Location: login.php?error=Invalid email or password");
                exit();
            }
        }
    } catch (PDOException $e) {
        header("Location: login.php?error=" . urlencode("Database error: " . $e->getMessage()));
        exit();
    } catch (Exception $e) {
        header("Location: login.php?error=" . urlencode("An error occurred. Please try again later."));
        exit();
    }
}
?>
