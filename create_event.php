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

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle event creation
    if (isset($_POST['create_event'])) {
        // Validate input
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

        // Additional date validation
        $date = DateTime::createFromFormat('Y-m-d', $event_date);
        if (!$date || $date->format('Y-m-d') !== $event_date) {
            $errors[] = "Invalid date format.";
        }

        if (empty($errors)) {
            // Insert new event
            $stmt = $pdo->prepare("
                INSERT INTO events (
                    name, description, event_date, event_time, cause,
                    location, max_volunteers, created_by, created_by_type, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'orphanage', 'active')
            ");
            
            try {
                $stmt->execute([
                    $name, $description, $event_date, $event_time, $cause,
                    $location, $max_volunteers, $_SESSION['orphanage_id']
                ]);
                $success = "Event created successfully!";
                header("refresh:2;url=my_events.php");
            } catch (PDOException $e) {
                $errors[] = "Failed to create event: " . $e->getMessage();
                error_log("Event Creation Error: " . $e->getMessage());
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
    <title>Create Event - CHARITEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Create New Event</h2>

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

        <form method="POST" class="needs-validation" novalidate>
            <div class="row g-3">
                <div class="col-12">
                    <label for="event_name" class="form-label">Event Name</label>
                    <input type="text" class="form-control" id="event_name" name="event_name" required>
                </div>

                <div class="col-12">
                    <label for="event_description" class="form-label">Description</label>
                    <textarea class="form-control" id="event_description" name="event_description" rows="3" required></textarea>
                </div>

                <div class="col-md-6">
                    <label for="event_date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="event_date" name="event_date" required>
                </div>

                <div class="col-md-6">
                    <label for="event_time" class="form-label">Time</label>
                    <input type="time" class="form-control" id="event_time" name="event_time" required>
                </div>

                <div class="col-md-6">
                    <label for="event_cause" class="form-label">Cause</label>
                    <select class="form-select" id="event_cause" name="event_cause" required>
                        <option value="">Choose...</option>
                        <option value="Educational Support">Educational Support</option>
                        <option value="Orphan Care">Orphan Care</option>
                        <option value="Elder Support">Elder Support</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="max_volunteers" class="form-label">Maximum Volunteers</label>
                    <input type="number" class="form-control" id="max_volunteers" name="max_volunteers" min="1" value="50" required>
                </div>

                <div class="col-12">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" required>
                </div>

                <div class="col-12">
                    <button type="submit" name="create_event" class="btn btn-primary">Create Event</button>
                    <a href="orphanage_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html> 