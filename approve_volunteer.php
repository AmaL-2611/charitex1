<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "charitex1";

// Create new connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['volunteer_id'];
    $verification_code = $_POST['verification_code'];
    
    try {
        // Get volunteer email
        $sql = "SELECT email FROM volunteer_verifications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $volunteer = $result->fetch_assoc();
        
        if ($volunteer) {
            // Update verification status
            $update_sql = "UPDATE volunteer_verifications 
                          SET status = 'approved', 
                              verification_code = ?, 
                              approval_date = NOW() 
                          WHERE id = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $verification_code, $id);
            
            if ($update_stmt->execute()) {
                // Create a new PHPMailer instance
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'amalbabu740@gmail.com'; // REPLACE with your Gmail address
                    $mail->Password = 'dezu oetk vhpz wktz';    // REPLACE with your Gmail App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('amalbabu740@gmail.com', 'CHARITEX Admin');
                    $mail->addAddress($volunteer['email']);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Volunteer Verification Approved';
                    $mail->Body = "
                        <h2>Volunteer Verification Approved!</h2>
                        <p>Dear Volunteer,</p>
                        <p>Your verification has been approved!</p>
                        <p>Your verification code is: <strong>{$verification_code}</strong></p>
                        <p>Please use this code to complete your registration.</p>
                        <br>
                        <p>Best regards,<br>CHARITEX Team</p>
                    ";

                    $mail->send();
                    echo "success: Code sent to " . $volunteer['email'];
                } catch (Exception $e) {
                    echo "error: Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                echo "error: Failed to update verification status";
            }
        } else {
            echo "error: Volunteer not found";
        }
    } catch (Exception $e) {
        echo "error: " . $e->getMessage();
    }
} else {
    echo "error: Invalid request method";
}

// Close the connection
$conn->close();
?> 