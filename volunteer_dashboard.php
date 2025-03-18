<?php
session_start();
require_once 'connect.php';

// Check if user is logged in and is a volunteer
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'volunteer') {
    header("Location: login.php");
    exit();
}

// Get volunteer details
try {
    // Use the database connection function from connect.php
    $pdo = getDatabaseConnection();
    
    if (!$pdo) {
        throw new Exception("Could not establish database connection.");
    }

    // Fetch registered events
    $events_stmt = $pdo->prepare("
        SELECT e.* FROM events e
        JOIN event_registrations er ON e.id = er.event_id
        WHERE er.volunteer_id = ? AND e.event_date >= CURDATE()
        ORDER BY e.event_date ASC
    ");
    $events_stmt->execute([$_SESSION['user_id']]);
    $registered_events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log($error);
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log($error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteer Dashboard - CHARITEX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .dashboard-header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        .event-card {
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
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Volunteer'); ?></h1>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            Event registration successful!
        </div>
    <?php endif; ?>

    <h2>Your Registered Events</h2>

    <?php if (!empty($registered_events)): ?>
        <?php foreach ($registered_events as $event): ?>
            <div class="event-card">
                <h3><?php echo htmlspecialchars($event['title'] ?? 'Unnamed Event'); ?></h3>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description'] ?? 'No description'); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You have not registered for any upcoming events.</p>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px;">
        <a href="event_registration.php" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Register for More Events
        </a>
        <a href="logout.php" style="background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">
            Logout
        </a>
    </div>
</body>
</html>
