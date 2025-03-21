<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "charitex1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // Define directory paths with full server path
    $base_dir = __DIR__;
    $uploads_dir = $base_dir . "/uploads";
    $target_dir = $uploads_dir . "/verifications";
    
    // Create directories if they don't exist
    if (!file_exists($uploads_dir)) {
        mkdir($uploads_dir, 0777);
    }
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777);
    }
    
    $file = $_FILES["verificationDoc"];
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . '/' . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $db_file_path = "uploads/verifications/" . $new_filename;
        $status = 'pending';
        $submission_date = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO volunteer_verifications (email, document_path, status, submission_date) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $_SESSION['error_message'] = "Database error occurred.";
            header("Location: volunteer_registration.php");
            exit();
        }
        
        $stmt->bind_param("ssss", $email, $db_file_path, $status, $submission_date);
        
        if ($stmt->execute()) {
            $stmt->close();
            $showThankYou = true;
        } else {
            error_log("Execute failed: " . $stmt->error);
            $stmt->close();
            $_SESSION['error_message'] = "Error uploading document to database.";
            header("Location: volunteer_registration.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Error uploading file. Please try again.";
        header("Location: volunteer_registration.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Verification - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: #e8f5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .icon-circle i {
            font-size: 2.5rem;
            color: #4caf50;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }

        .message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn-custom {
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
            margin: 10px;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26,42,108,0.2);
            color: white;
        }

        .divider {
            height: 1px;
            background: #eee;
            margin: 2rem 0;
        }

        .footer-text {
            color: #888;
            font-size: 0.9rem;
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
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($showThankYou) && $showThankYou): ?>
            <div class="icon-circle">
                <i class="fas fa-check"></i>
            </div>
            
            <h1>Thank You!</h1>
            
            <div class="message">
                <p>Thank you for uploading your police verification document. Our team will review it shortly.</p>
                <p>Your account status has been set to pending while we verify your documents.</p>
                <p>We will notify you once the verification is complete.</p>
            </div>

            <div class="divider"></div>

            <p class="footer-text">
                Best regards,<br>
                CHARITEX Team
            </p>

            <div class="mt-4">
                <a href="index.php" class="btn-custom">
                    <i class="fas fa-home me-2"></i>Return to Home
                </a>
                <a href="volunteer_signup.php" class="btn-custom">
                    <i class="fas fa-user-plus me-2"></i>Continue to Sign Up
                </a>
            </div>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data">
                <!-- ... your existing form fields ... -->
            </form>
        <?php endif; ?>
    </div>
</body>
</html> 