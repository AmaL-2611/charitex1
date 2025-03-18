<?php
session_start();
require_once 'config.php';

// Check if orphanage is logged in
if (!isset($_SESSION['orphanage_id'])) {
    header('location:login_orphanage.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch orphanage's events
    $events_stmt = $pdo->prepare("
        SELECT e.*, 
            (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registered_volunteers
        FROM events e 
        WHERE e.created_by = ? 
        AND e.created_by_type = 'orphanage'
        ORDER BY e.event_date ASC
    ");
    $events_stmt->execute([$_SESSION['orphanage_id']]);
    $events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $error_message = "An error occurred while fetching the events.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Events</h2>
            <a href="create_event.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Event
            </a>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Cause</th>
                            <th>Volunteers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No events found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($event['event_date'])); ?></td>
                                    <td><?php echo $event['event_time']; ?></td>
                                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                                    <td><?php echo htmlspecialchars($event['cause']); ?></td>
                                    <td><?php echo $event['registered_volunteers'] . '/' . $event['max_volunteers']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-info" onclick="viewVolunteers(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-users"></i>
                                            </button>
                                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-3">
            <a href="orphanage_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                window.location.href = `delete_event.php?id=${eventId}`;
            }
        }

        function viewVolunteers(eventId) {
            // Implement volunteer viewing functionality
            alert('Viewing volunteers for event ' + eventId);
        }
    </script>
</body>
</html> 