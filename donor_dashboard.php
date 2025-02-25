<?php
session_start();
require_once 'connect.php';

// Check if user is logged in and is a donor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'donor') {
    header("Location: login.php");
    exit();
}

// Get donor details
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch recent donations
    $donations_stmt = $pdo->prepare("
        SELECT * FROM donations 
        WHERE donor_id = ? 
        ORDER BY donation_date DESC 
        LIMIT 10
    ");
    $donations_stmt->execute([$_SESSION['user_id']]);
    $recent_donations = $donations_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error fetching donations: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor Dashboard - CHARITEX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .dashboard-header {
            background-color: #28a745;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        .donation-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success-message {
            background-color: #28a745;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Donor'); ?></h1>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            Event registration successful!
        </div>
    <?php endif; ?>

    <h2>Your Recent Donations</h2>

    <?php if (!empty($recent_donations)): ?>
        <?php foreach ($recent_donations as $donation): ?>
            <div class="donation-card">
                <h3>Donation to <?php echo htmlspecialchars($donation['cause'] ?? 'Unnamed Cause'); ?></h3>
                <p><strong>Amount:</strong> $<?php echo number_format($donation['amount'], 2); ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($donation['donation_date'])); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($donation['status'] ?? 'Processed'); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You have not made any donations yet.</p>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px;">
        <a href="donate.php" style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Make a Donation
        </a>
        <a href="logout.php" style="background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">
            Logout
        </a>
    </div>
</body>
</html>
