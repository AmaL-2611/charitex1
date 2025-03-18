<?php
session_start();
require_once 'connect.php';
require_once 'config.php';

// Ensure only admin can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin')
 {
    header("Location: login.php");
    exit();
}

// Initialize variables
$errors = [];
$success = '';
$edit_event = null;

// Remove any local getDatabaseConnection function if present
// Database connection is now handled in connect.php

// Handle event operations
try {
    $pdo = $pdo; // Assuming $pdo is defined in connect.php
    if (!$pdo) {
        throw new Exception("Could not establish database connection.");
    }

    // Add approval_status column if it doesn't exist
    try {
        $pdo->exec("
            ALTER TABLE events 
            ADD COLUMN IF NOT EXISTS approval_status VARCHAR(20) DEFAULT 'pending' AFTER status
        ");
    } catch (PDOException $e) {
        error_log("Table modification error: " . $e->getMessage());
    }

    // Delete event with cascading
    if (isset($_GET['delete_event'])) {
        $event_id = intval($_GET['delete_event']);

        // Start transaction for safe deletion
        $pdo->beginTransaction();

        try {
            // First, delete related registrations
            $delete_registrations = $pdo->prepare("DELETE FROM event_registrations WHERE event_id = :event_id");
            $delete_registrations->execute([':event_id' => $event_id]);

            // Then delete the event
            $delete_event = $pdo->prepare("DELETE FROM events WHERE id = :event_id");
            $delete_event->execute([':event_id' => $event_id]);

            // Commit the transaction
            $pdo->commit();

            $success = "Event and its registrations deleted successfully!";
            
            // Log the deletion
            error_log("Event $event_id deleted by admin at " . date('Y-m-d H:i:s'));
        } catch (PDOException $e) {
            // Rollback the transaction on error
            $pdo->rollBack();
            $errors[] = "Error deleting event: " . $e->getMessage();
            
            // Log the error
            error_log("Event Deletion Error: " . $e->getMessage());
        }
    }

    // Add new event
    if (isset($_POST['create_event'])) {
        // Comprehensive validation
        $name = trim($_POST['event_name'] ?? '');
        $description = trim($_POST['event_description'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $event_time = $_POST['event_time'] ?? '';
        $cause = trim($_POST['event_cause'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $max_volunteers = intval($_POST['max_volunteers'] ?? 50);

        // Reset errors array
        $errors = [];

        // Validate each field
        if (empty($name)) {
            $errors[] = "Event name is required.";
        }

        if (empty($description)) {
            $errors[] = "Event description is required.";
        }

        if (empty($event_date)) {
            $errors[] = "Event date is required.";
        } else {
            // Additional date validation
            $date = DateTime::createFromFormat('Y-m-d', $event_date);
            if (!$date || $date->format('Y-m-d') !== $event_date) {
                $errors[] = "Invalid date format. Use YYYY-MM-DD.";
            }
        }

        if (empty($event_time)) {
            $errors[] = "Event time is required.";
        }

        // Validate cause with predefined options
        $valid_causes = [
            'Educational Support', 
            'Orphan Care', 
            'Elder Support'
        ];
        if (empty($cause) || !in_array($cause, $valid_causes)) {
            $errors[] = "Please select a valid event cause.";
        }

        if (empty($location)) {
            $errors[] = "Event location is required.";
        }

        if ($max_volunteers < 1) {
            $errors[] = "Maximum volunteers must be at least 1.";
        }

        // If there are validation errors, stop processing
        if (!empty($errors)) {
            // Errors will be displayed by the previously added error handling code
            return;
        }

        try {
            // Insert new event
            $stmt = $pdo->prepare("
                INSERT INTO events 
                (name, description, event_date, event_time, cause, location, max_volunteers, status) 
                VALUES 
                (:name, :description, :event_date, :event_time, :cause, :location, :max_volunteers, 'active')
            ");

            $result = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':event_date' => $event_date,
                ':event_time' => $event_time,
                ':cause' => $cause,
                ':location' => $location,
                ':max_volunteers' => $max_volunteers
            ]);

            if ($result) {
                $success = "Event created successfully!";
                error_log("Event Created: " . $name);
            } else {
                $errors[] = "Failed to create event. Please try again.";
                error_log("Event Creation Failed: " . print_r($stmt->errorInfo(), true));
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
            error_log("Event Creation PDO Error: " . $e->getMessage());
        }
    }

    // Update existing event
    if (isset($_POST['update_event'])) {
        $event_id = intval($_POST['event_id']);
        $name = trim($_POST['event_name'] ?? '');
        $description = trim($_POST['event_description'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $event_time = $_POST['event_time'] ?? '';
        $cause = trim($_POST['event_cause'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $max_volunteers = intval($_POST['max_volunteers'] ?? 50);
        $status = $_POST['event_status'] ?? 'upcoming';

        // Validation
        if (empty($name)) $errors[] = "Event name is required.";
        if (empty($event_date)) $errors[] = "Event date is required.";
        if (empty($event_time)) $errors[] = "Event time is required.";
        if (empty($cause)) $errors[] = "Event cause is required.";
        if (empty($location)) $errors[] = "Event location is required.";

        // Validate cause with predefined options
        $valid_causes = [
            'Educational Support', 
            'Orphan Care', 
            'Elder Support', 
            
        ];
        if (empty($cause) || !in_array($cause, $valid_causes)) {
            $errors[] = "Please select a valid event cause.";
        }

        // Check for duplicate events (excluding current event)
        $duplicate_check = $pdo->prepare("
            SELECT id FROM events 
            WHERE name = :name 
            AND event_date = :event_date 
            AND location = :location 
            AND id != :event_id
        ");
        $duplicate_check->execute([
            ':name' => $name,
            ':event_date' => $event_date,
            ':location' => $location,
            ':event_id' => $event_id
        ]);

        if ($duplicate_check->rowCount() > 0) {
            $errors[] = "An event with the same name, date, and location already exists.";
        }

        // Additional date validation
        $current_date = date('Y-m-d');
        if (strtotime($event_date) < strtotime($current_date)) {
            $errors[] = "Event date cannot be in the past.";
        }

        if (empty($errors)) {
            // Prepare and execute the update
            $stmt = $pdo->prepare("
                UPDATE events 
                SET name = :name, 
                    description = :description, 
                    event_date = :event_date, 
                    event_time = :event_time, 
                    cause = :cause, 
                    location = :location, 
                    max_volunteers = :max_volunteers,
                    status = :status
                WHERE id = :event_id
            ");

            $result = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':event_date' => $event_date,
                ':event_time' => $event_time,
                ':cause' => $cause,
                ':location' => $location,
                ':max_volunteers' => $max_volunteers,
                ':status' => $status,
                ':event_id' => $event_id
            ]);

            $success = "Event updated successfully!";
        }
    }

    // Handle edit event (load event details)
    if (isset($_GET['edit_event'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :event_id");
            $stmt->execute([':event_id' => intval($_GET['edit_event'])]);
            $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = "Error fetching event details: " . $e->getMessage();
        }
    }

    // Fetch events with registered volunteers
    // Dynamically detect column names
    $volunteers_columns = [];
    try {
        $columns_stmt = $pdo->query("SHOW COLUMNS FROM volunteers");
        $columns = $columns_stmt->fetchAll(PDO::FETCH_COLUMN);

        // Define possible column mappings
        $column_mappings = [
            'name' => ['name', 'full_name', 'username'],
            'first_name' => ['first_name', 'firstname', 'fname'],
            'last_name' => ['last_name', 'lastname', 'lname'],
            'email' => ['email', 'user_email', 'contact_email'],
            'phone' => ['phone', 'phone_number', 'contact_number'],
            'location' => ['location', 'city', 'address']
        ];

        // Find matching columns
        foreach ($column_mappings as $key => $possible_columns) {
            foreach ($possible_columns as $col) {
                if (in_array($col, $columns)) {
                    $volunteers_columns[$key] = $col;
                    break;
                }
            }
        }

        // Construct dynamic query for events and volunteers
        $select_name = isset($volunteers_columns['first_name']) && isset($volunteers_columns['last_name']) 
            ? "CONCAT(v.{$volunteers_columns['first_name']}, ' ', v.{$volunteers_columns['last_name']})" 
            : (isset($volunteers_columns['name']) ? "v.{$volunteers_columns['name']}" : "'Unknown'");

        $events_query = "
            SELECT e.*, 
                   COUNT(er.volunteer_id) as registered_volunteers,
                   GROUP_CONCAT(DISTINCT $select_name SEPARATOR ', ') as volunteer_names
            FROM events e
            LEFT JOIN event_registrations er ON e.id = er.event_id
            LEFT JOIN volunteers v ON er.volunteer_id = v.id
            GROUP BY e.id
            ORDER BY e.event_date DESC
        ";
        $events_stmt = $pdo->prepare($events_query);
        $events_stmt->execute();
        $events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Log detected columns for debugging
        error_log("Detected Volunteers Columns: " . print_r($volunteers_columns, true));

    } catch (PDOException $e) {
        $errors[] = "Error fetching events: " . $e->getMessage();
        error_log("Events Fetch Error: " . $e->getMessage());
        $events = [];
        $volunteers_columns = [];
    }

    // Handle approval/rejection
    if (isset($_POST['action']) && isset($_POST['event_id'])) {
        $event_id = intval($_POST['event_id']);
        $action = $_POST['action'];
        
        if ($action === 'approve' || $action === 'reject') {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            
            $stmt = $pdo->prepare("
                UPDATE events 
                SET approval_status = :status 
                WHERE id = :event_id
            ");
            
            if ($stmt->execute([':status' => $status, ':event_id' => $event_id])) {
                $success = "Event has been " . $status;
            } else {
                $errors[] = "Failed to " . $action . " event";
            }
        }
    }

    // Fetch pending events
    $stmt = $pdo->prepare("
        SELECT e.*, o.name as orphanage_name, o.location as orphanage_location
        FROM events e
        JOIN orphanage o ON e.created_by = o.id
        WHERE e.created_by_type = 'orphanage' 
        AND (e.approval_status = 'pending' OR e.approval_status IS NULL)
        ORDER BY e.event_date ASC
    ");
    $stmt->execute();
    $pending_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $errors[] = "Error processing request: " . $e->getMessage();
    error_log("Admin Events Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Event Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .event-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-12 px-md-4">
                <h2 class="my-4">Pending Event Approvals</h2>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($pending_events)): ?>
                    <div class="alert alert-info">No pending events to approve.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($pending_events as $event): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card event-card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['name']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Orphanage:</strong> <?php echo htmlspecialchars($event['orphanage_name']); ?></p>
                                        <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($event['event_date'])); ?></p>
                                        <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($event['event_time'])); ?></p>
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                        <p><strong>Cause:</strong> <?php echo htmlspecialchars($event['cause']); ?></p>
                                        <p><strong>Max Volunteers:</strong> <?php echo htmlspecialchars($event['max_volunteers']); ?></p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
                                        
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-approve flex-grow-1">
                                                Approve
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn btn-reject flex-grow-1">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?php 
    // Comprehensive error logging
    if (!empty($errors)) {
        // Log errors to PHP error log
        foreach ($errors as $error) {
            error_log("Event Creation Error: " . $error);
        }
        
        // Display errors in the form
        echo '<div class="alert alert-danger">';
        echo '<strong>Please correct the following errors:</strong>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo '</ul>';
        echo '</div>';
    }

    // Debug logging for form submission
    error_log("POST Data: " . print_r($_POST, true));
    ?>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
