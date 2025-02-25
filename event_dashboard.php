<?php
session_start();
require_once 'connect.php';



// Handle marking event status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $event_id = intval($_POST['event_id']);
    $new_status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE events SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $event_id]);
    } catch (PDOException $e) {
        $error = "Error updating event status: " . $e->getMessage();
    }
}

// Fetch events with registration details
try {
    $stmt = $pdo->prepare("
        SELECT 
            e.id, 
            e.title, 
            e.event_date, 
            e.status, 
            e.total_volunteer_slots,
            e.available_slots,
            COUNT(er.id) as registered_volunteers
        FROM 
            events e
        LEFT JOIN 
            event_registrations er ON e.id = er.event_id
        GROUP BY 
            e.id, e.title, e.event_date, e.status, 
            e.total_volunteer_slots, e.available_slots
        ORDER BY 
            e.event_date DESC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching events: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Dashboard - CHARITEX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-select {
            width: 100%;
            padding: 5px;
        }
        .status-upcoming { color: blue; }
        .status-ongoing { color: orange; }
        .status-completed { color: green; }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Event Dashboard</h1>

    <?php if (isset($error)): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Event Title</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total Slots</th>
                <th>Available Slots</th>
                <th>Registered Volunteers</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($events)): ?>
                <tr>
                    <td colspan="7">No events found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo date('F j, Y', strtotime($event['event_date'])); ?></td>
                        <td class="status-<?php echo htmlspecialchars(strtolower($event['status'])); ?>">
                            <?php echo htmlspecialchars($event['status']); ?>
                        </td>
                        <td><?php echo $event['total_volunteer_slots']; ?></td>
                        <td><?php echo $event['available_slots']; ?></td>
                        <td><?php echo $event['registered_volunteers']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <select name="status" class="status-select" onchange="this.form.submit()">
                                    <option value="upcoming" <?php echo $event['status'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    <option value="ongoing" <?php echo $event['status'] === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="completed" <?php echo $event['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
