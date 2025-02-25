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
    if (!isset($pdo)) {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .event-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
        }
        .btn-unregister {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-unregister:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="dashboard-header">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Volunteer'); ?>!</h2>
            <a href="logout.php" class="btn btn-danger">Logout</a>
            <a href="process_registration.php" class="btn btn-primary">Browse Events</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                Event registration successful!
            </div>
        <?php endif; ?>

        <h3>Your Registered Events</h3>
        
        <?php if (empty($registered_events)): ?>
            <div class="alert alert-info">
                You haven't registered for any upcoming events yet. 
                <a href="process_registration.php">Browse available events</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($registered_events as $event): ?>
                    <div class="col-md-6">
                        <div class="event-card">
                            <h4><?php echo htmlspecialchars($event['title'] ?? 'Unnamed Event'); ?></h4>
                            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description'] ?? 'No description'); ?></p>
                            <form action="process_unregister.php" method="POST" style="display: inline;">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button type="submit" class="btn btn-unregister" 
                                        onclick="return confirm('Are you sure you want to unregister from this event?')">
                                    Unregister
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
