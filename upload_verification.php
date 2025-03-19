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
            $_SESSION['success_message'] = "Document uploaded successfully. Please wait for admin approval.";
            header("Location: volunteer_signup.php");
            exit();
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