<?php
session_start();
require_once 'config.php';

// Check if orphanage is logged in
if (!isset($_SESSION['orphanage_id'])) {
    header('location:login_orphanage.php');
    exit();
}

$errors = [];
$success = '';
$event = null;

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the event details
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT * FROM events 
            WHERE id = ? AND created_by = ? AND created_by_type = 'orphanage'
        ");
        $stmt->execute([$_GET['id'], $_SESSION['orphanage_id']]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            header('location:my_events.php');
            exit();
        }
    }

    // Handle form submission
    if (isset($_POST['update_event'])) {
        $name = trim($_POST['event_name'] ?? '');
        $description = trim($_POST['event_description'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $event_time = $_POST['event_time'] ?? '';
        $cause = trim($_POST['event_cause'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $max_volunteers = intval($_POST['max_volunteers'] ?? 50);

        // Validation
        if (empty($name)) $errors[] = "Event name is required.";
        if (empty($description)) $errors[] = "Event description is required.";
        if (empty($event_date)) $errors[] = "Event date is required.";
        if (empty($event_time)) $errors[] = "Event time is required.";
        if (empty($cause)) $errors[] = "Cause is required.";
        if (empty($location)) $errors[] = "Location is required.";
        if ($max_volunteers < 1) $errors[] = "Maximum volunteers must be at least 1.";

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE events 
                    SET name = ?, description = ?, event_date = ?, 
                        event_time = ?, cause = ?, location = ?, 
                        max_volunteers = ?
                    WHERE id = ? AND created_by = ? AND created_by_type = 'orphanage'
                ");
                
                $stmt->execute([
                    $name, $description, $event_date, $event_time,
                    $cause, $location, $max_volunteers,
                    $_GET['id'], $_SESSION['orphanage_id']
                ]);
                
                $success = "Event updated successfully!";
                header("refresh:2;url=my_events.php");
            } catch (PDOException $e) {
                error_log("Update Error: " . $e->getMessage());
                $errors[] = "Failed to update event. Please try again.";
            }
        }
    }
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Event</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($event): ?>
            <form method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="event_name" class="form-label">Event Name</label>
                        <input type="text" class="form-control" id="event_name" name="event_name" 
                               value="<?php echo htmlspecialchars($event['name']); ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="event_description" class="form-label">Description</label>
                        <textarea class="form-control" id="event_description" name="event_description" 
                                rows="3" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="event_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="event_date" name="event_date" 
                               value="<?php echo $event['event_date']; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="event_time" class="form-label">Time</label>
                        <input type="time" class="form-control" id="event_time" name="event_time" 
                               value="<?php echo $event['event_time']; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="event_cause" class="form-label">Cause</label>
                        <select class="form-select" id="event_cause" name="event_cause" required>
                            <option value="">Choose...</option>
                            <option value="Educational Support" <?php echo $event['cause'] === 'Educational Support' ? 'selected' : ''; ?>>Educational Support</option>
                            <option value="Orphan Care" <?php echo $event['cause'] === 'Orphan Care' ? 'selected' : ''; ?>>Orphan Care</option>
                            <option value="Elder Support" <?php echo $event['cause'] === 'Elder Support' ? 'selected' : ''; ?>>Elder Support</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="max_volunteers" class="form-label">Maximum Volunteers</label>
                        <input type="number" class="form-control" id="max_volunteers" name="max_volunteers" 
                               min="1" value="<?php echo $event['max_volunteers']; ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               value="<?php echo htmlspecialchars($event['location']); ?>" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" name="update_event" class="btn btn-primary">Update Event</button>
                        <a href="my_events.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 