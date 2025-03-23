<?php
// Add these at the top of your file
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoload file
require 'vendor/autoload.php';

session_start();
require_once 'connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['application_id'])) {
    try {
        $application_id = $_POST['application_id'];
        $action = $_POST['action'];
        
        if ($action === 'approve') {
            try {
                // Get application details
                $stmt = $pdo->prepare("SELECT * FROM volunteer_applications WHERE id = ?");
                $stmt->execute([$application_id]);
                $application = $stmt->fetch();

                if ($application) {
                    // Begin transaction
                    $pdo->beginTransaction();

                    try {
                        // Insert into volunteers table
                        $insert_sql = "INSERT INTO volunteers (
                            name, email, mobile, password, location, 
                            availability, aadhar_file, police_doc, status
                        ) VALUES (
                            :name, :email, :mobile, :password, :location,
                            :availability, :aadhar_file, :police_doc, 'active'
                        )";

                        $insert_stmt = $pdo->prepare($insert_sql);
                        $result = $insert_stmt->execute([
                            ':name' => $application['name'],
                            ':email' => $application['email'],
                            ':mobile' => $application['mobile'],
                            ':password' => $application['password'],
                            ':location' => $application['location'],
                            ':availability' => $application['availability'],
                            ':aadhar_file' => $application['aadhar_file'],
                            ':police_doc' => $application['police_doc']
                        ]);

                        if (!$result) {
                            throw new Exception("Failed to insert into volunteers table");
                        }

                        // Update application status
                        $update_stmt = $pdo->prepare("UPDATE volunteer_applications SET status = 'approved' WHERE id = ?");
                        $result = $update_stmt->execute([$application_id]);

                        if (!$result) {
                            throw new Exception("Failed to update application status");
                        }

                        // Create a new PHPMailer instance
                        $mail = new PHPMailer(true);

                        try {
                            // Server settings
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP host
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'amalbabu740@gmail.com'; // Replace with your email
                            $mail->Password   = 'dezu oetk vhpz wktz'; // Replace with your password
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;

                            // Recipients
                            $mail->setFrom('amalbabu740@gmail.com', 'Charitex Team');
                            $mail->addAddress($application['email'], $application['name']);
                            $mail->addReplyTo('amalbabu740@gmail.com', 'Charitex Team');

                            // Content
                            $mail->isHTML(true);
                            $mail->Subject = "Volunteer Application Approved - Welcome to Charitex!";

                            // Get the website base URL
                            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
                            $base_url = rtrim($base_url, '/');
                            $login_url = $base_url . "/login.php";

                            // Email body
                            $mail->Body = "
                            <html>
                            <head>
                                <style>
                                    body { 
                                        font-family: Arial, sans-serif; 
                                        line-height: 1.6; 
                                        color: #333;
                                    }
                                    .container { 
                                        max-width: 600px; 
                                        margin: 0 auto; 
                                        padding: 20px; 
                                        background-color: #f9f9f9;
                                    }
                                    .header {
                                        background-color: #4CAF50;
                                        color: white;
                                        padding: 20px;
                                        text-align: center;
                                        border-radius: 5px 5px 0 0;
                                    }
                                    .content {
                                        background-color: white;
                                        padding: 20px;
                                        border-radius: 0 0 5px 5px;
                                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                                    }
                                    .button { 
                                        background-color: #4CAF50; 
                                        color: white; 
                                        padding: 12px 25px; 
                                        text-decoration: none; 
                                        border-radius: 5px; 
                                        display: inline-block; 
                                        margin: 20px 0;
                                    }
                                    .footer { 
                                        margin-top: 30px; 
                                        font-size: 0.9em; 
                                        color: #666; 
                                        text-align: center;
                                    }
                                    .info-box {
                                        background-color: #f5f5f5;
                                        padding: 15px;
                                        border-radius: 5px;
                                        margin: 15px 0;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class='container'>
                                    <div class='header'>
                                        <h2>Welcome to Charitex!</h2>
                                    </div>
                                    <div class='content'>
                                        <p>Dear " . htmlspecialchars($application['name']) . ",</p>
                                        
                                        <p>Congratulations! Your volunteer application has been approved. We're excited to have you join our team of dedicated volunteers.</p>
                                        
                                        <div class='info-box'>
                                            <p><strong>Login Details:</strong></p>
                                            <ul>
                                                <li>Email: " . htmlspecialchars($application['email']) . "</li>
                                                <li>Password: (the one you set during registration)</li>
                                            </ul>
                                        </div>

                                        <p style='text-align: center;'>
                                            <a href='localhost/project2/login.php' class='button' style='color: white;'>
                                                Click here to Login
                                            </a>
                                        </p>

                                        

                                        <div class='footer'>
                                            <p>Best regards,<br>Charitex Team</p>
                                            <p style='font-size: 0.8em; color: #999;'>
                                                If you didn't register for a volunteer account, please ignore this email.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </body>
                            </html>";

                            // Plain text version for non-HTML mail clients
                            $mail->AltBody = "
                            Welcome to Charitex!
                            
                            Dear " . $application['name'] . ",
                            
                            Congratulations! Your volunteer application has been approved. We're excited to have you join our team of dedicated volunteers.
                            
                            You can now login to your volunteer account using:
                            Email: " . $application['email'] . "
                            Password: (the one you set during registration)
                            
                            Login URL: " . $login_url . "
                            
                            Best regards,
                            Charitex Team";

                            $mail->send();
                            $pdo->commit();
                            $_SESSION['message'] = "Volunteer application approved and welcome email sent successfully.";

                        } catch (Exception $e) {
                            error_log("Email sending failed: " . $mail->ErrorInfo);
                            // Still commit the transaction but notify about email failure
                            $pdo->commit();
                            $_SESSION['message'] = "Volunteer application approved but there was an issue sending the welcome email.";
                        }

                    } catch (Exception $e) {
                        $pdo->rollBack();
                        error_log("Error in approval process: " . $e->getMessage());
                        $_SESSION['error'] = "Error processing the approval: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['error'] = "Application not found.";
                }
            } catch (PDOException $e) {
                error_log("Database error in approval process: " . $e->getMessage());
                $_SESSION['error'] = "Database error during approval process.";
            }
        } else {
            // Handle rejection
            $stmt = $pdo->prepare("
                UPDATE volunteer_applications 
                SET status = 'rejected' 
                WHERE id = ?
            ");
            $stmt->execute([$application_id]);

            // Get applicant email
            $email_stmt = $pdo->prepare("SELECT email, name FROM volunteer_applications WHERE id = ?");
            $email_stmt->execute([$application_id]);
            $applicant = $email_stmt->fetch();

            if ($applicant) {
                // Send rejection email
                $to = $applicant['email'];
                $subject = "Volunteer Application Status";
                $message = "Dear " . $applicant['name'] . ",\n\n";
                $message .= "Thank you for your interest in volunteering with us. Unfortunately, we cannot accept your application at this time.\n\n";
                $message .= "Best regards,\nCharitex Team";
                
                mail($to, $subject, $message);
            }

            $_SESSION['message'] = "Volunteer application rejected.";
        }

        header('Location: volunteer_requests.php');
        exit();
    } catch (PDOException $e) {
        error_log("Error processing volunteer application: " . $e->getMessage());
        $_SESSION['error'] = "Error processing the application.";
    }
}

// Fetch pending applications
try {
    $stmt = $pdo->prepare("
        SELECT * FROM volunteer_applications 
        WHERE status = 'pending'
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $pending_applications = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching pending applications: " . $e->getMessage());
    $pending_applications = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Requests - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            padding: 20px 0;
        }
        .content {
            padding: 20px;
        }
        .volunteer-card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .document-preview {
            max-width: 200px;
            margin: 10px 0;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 5px 10px;
            border-radius: 15px;
        }
        .status-pending { background-color: #ffc107; }
        .status-approved { background-color: #28a745; }
        .status-rejected { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include your sidebar here -->
           

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Volunteer Requests</h1>
                    </div>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($pending_applications)): ?>
                        <div class="alert alert-info">
                            No pending volunteer requests at this time.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($pending_applications as $application): ?>
                                <div class="col-12 mb-4">
                                    <div class="card volunteer-card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h5 class="card-title">
                                                        <?php echo htmlspecialchars($application['name']); ?>
                                                        <span class="badge bg-warning text-dark ms-2">Pending</span>
                                                    </h5>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
                                                            <p><strong>Mobile:</strong> <?php echo htmlspecialchars($application['mobile']); ?></p>
                                                            <p><strong>Location:</strong> <?php echo htmlspecialchars($application['location']); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Availability:</strong> <?php echo htmlspecialchars($application['availability']); ?></p>
                                                            <p><strong>Applied On:</strong> <?php echo date('M d, Y', strtotime($application['created_at'])); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="document-links mb-3">
                                                        <a href="<?php echo htmlspecialchars($application['aadhar_file']); ?>" 
                                                           class="btn btn-outline-primary btn-sm mb-2" 
                                                           target="_blank">
                                                            <i class="fas fa-file-alt"></i> View Aadhar Document
                                                        </a>
                                                        <a href="<?php echo htmlspecialchars($application['police_doc']); ?>" 
                                                           class="btn btn-outline-primary btn-sm" 
                                                           target="_blank">
                                                            <i class="fas fa-file-alt"></i> View Police Verification
                                                        </a>
                                                    </div>
                                                    <div class="action-buttons">
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="btn btn-success me-2">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm before rejecting
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (this.querySelector('input[name="action"]').value === 'reject') {
                    if (!confirm('Are you sure you want to reject this volunteer application?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>
</html> 