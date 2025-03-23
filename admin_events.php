<?php
session_start();
require_once 'connect.php';
require_once 'config.php';

<<<<<<< HEAD
// Define Kerala districts
$districts = [
    'Thiruvananthapuram', 'Kollam', 'Pathanamthitta', 'Alappuzha',
    'Kottayam', 'Idukki', 'Ernakulam', 'Thrissur',
    'Palakkad', 'Malappuram', 'Kozhikode', 'Wayanad',
    'Kannur', 'Kasaragod'
];

// Get current date for date input min attribute
$current_date = date('Y-m-d');

=======
>>>>>>> master
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

<<<<<<< HEAD
=======
    // Add approval_status column if it doesn't exist
    try {
        $pdo->exec("
            ALTER TABLE events 
            ADD COLUMN IF NOT EXISTS approval_status VARCHAR(20) DEFAULT 'pending' AFTER status
        ");
    } catch (PDOException $e) {
        error_log("Table modification error: " . $e->getMessage());
    }

>>>>>>> master
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

<<<<<<< HEAD
    // Create event
    if (isset($_POST['create_event'])) {
        try {
            // Get event details from form
            $title = trim($_POST['event_name'] ?? '');
            $description = trim($_POST['event_description'] ?? '');
            $event_date = $_POST['event_date'] ?? '';
            $event_time = $_POST['event_time'] ?? '';
            $cause = $_POST['event_cause'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $max_volunteers = intval($_POST['max_volunteers'] ?? 50);

            // Event Image Upload
            $event_image_path = null;
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB

                if (in_array($_FILES['event_image']['type'], $allowed_types) && 
                    $_FILES['event_image']['size'] <= $max_size) {
                    
                    // Create uploads directory if it doesn't exist
                    $upload_dir = 'uploads/event_images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Generate unique filename
                    $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'event_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;

                    // Move uploaded file
                    if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                        $event_image_path = $upload_path;
                    } else {
                        $errors[] = "Failed to upload event image.";
                    }
                } else {
                    $errors[] = "Invalid event image type or size. Please upload a JPEG, PNG, or GIF under 5MB.";
                }
            }

            // Reset errors array
            $errors = [];

            // Validate each field
            if (empty($title)) {
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
                // Prepare and execute the insert
                $stmt = $pdo->prepare("
                    INSERT INTO events 
                    (title, description, event_date, event_time, cause, location, max_volunteers, current_volunteers, status, image_path) 
                    VALUES 
                    (:title, :description, :event_date, :event_time, :cause, :location, :max_volunteers, 0, 'upcoming', :image_path)
                ");

                $result = $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':event_date' => $event_date,
                    ':event_time' => $event_time,
                    ':cause' => $cause,
                    ':location' => $location,
                    ':max_volunteers' => $max_volunteers,
                    ':image_path' => $event_image_path
                ]);

                if (!$result) {
                    throw new Exception("Failed to create event");
                }
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
                error_log("Event Creation Error: " . $e->getMessage());
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
            error_log("Event Creation Error: " . $e->getMessage());
=======
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
>>>>>>> master
        }
    }

    // Update existing event
    if (isset($_POST['update_event'])) {
<<<<<<< HEAD
        try {
            // Get event details from form
            $title = trim($_POST['event_name'] ?? '');
            $description = trim($_POST['event_description'] ?? '');
            $event_date = $_POST['event_date'] ?? '';
            $event_time = $_POST['event_time'] ?? '';
            $cause = $_POST['event_cause'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $max_volunteers = intval($_POST['max_volunteers'] ?? 50);
            $event_id = intval($_POST['event_id'] ?? 0);
            $status = $_POST['event_status'] ?? 'upcoming';

            // Event Image Upload
            $event_image_path = null;
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB

                if (in_array($_FILES['event_image']['type'], $allowed_types) && 
                    $_FILES['event_image']['size'] <= $max_size) {
                    
                    // Create uploads directory if it doesn't exist
                    $upload_dir = 'uploads/event_images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Generate unique filename
                    $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'event_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;

                    // Move uploaded file
                    if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                        $event_image_path = $upload_path;
                    } else {
                        $errors[] = "Failed to upload event image.";
                    }
                } else {
                    $errors[] = "Invalid event image type or size. Please upload a JPEG, PNG, or GIF under 5MB.";
                }
            }

            // Validation
            if (empty($title)) $errors[] = "Event name is required.";
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
                WHERE title = :title 
                AND event_date = :event_date 
                AND location = :location 
                AND id != :event_id
            ");
            $duplicate_check->execute([
                ':title' => $title,
                ':event_date' => $event_date,
                ':location' => $location,
                ':event_id' => $event_id
            ]);

            if ($duplicate_check->rowCount() > 0) {
                $errors[] = "An event with the same name, date, location and district already exists.";
            }

            // Additional date validation
            $current_date = date('Y-m-d');
            if (strtotime($event_date) < strtotime($current_date)) {
                $errors[] = "Event date cannot be in the past.";
            }

            if (empty($errors)) {
                try {
                    // First, get the current number of registered volunteers
                    $registered_stmt = $pdo->prepare("
                        SELECT COUNT(*) 
                        FROM event_registrations 
                        WHERE event_id = :event_id
                    ");
                    $registered_stmt->execute([':event_id' => $event_id]);
                    $current_volunteers = $registered_stmt->fetchColumn();

                    // Calculate available slots
                    $available_slots = max(0, $max_volunteers - $current_volunteers);

                    // Prepare and execute the update
                    $stmt = $pdo->prepare("
                        UPDATE events 
                        SET title = :title, 
                            description = :description, 
                            event_date = :event_date, 
                            event_time = :event_time, 
                            cause = :cause, 
                            location = :location,
                            max_volunteers = :max_volunteers,
                            current_volunteers = :current_volunteers,
                            status = :status,
                            image_path = :image_path
                        WHERE id = :event_id
                    ");

                    $result = $stmt->execute([
                        ':title' => $title,
                        ':description' => $description,
                        ':event_date' => $event_date,
                        ':event_time' => $event_time,
                        ':cause' => $cause,
                        ':location' => $location,
                        ':max_volunteers' => $max_volunteers,
                        ':current_volunteers' => $current_volunteers,
                        ':status' => $status,
                        ':image_path' => $event_image_path,
                        ':event_id' => $event_id
                    ]);

                    if (!$result) {
                        throw new Exception("Failed to update event");
                    }
                } catch (PDOException $e) {
                    $errors[] = "Database error: " . $e->getMessage();
                    error_log("Event Update Error: " . $e->getMessage());
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
            error_log("Event Update Error: " . $e->getMessage());
=======
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
>>>>>>> master
        }
    }

    // Handle edit event (load event details)
    if (isset($_GET['edit_event'])) {
        try {
<<<<<<< HEAD
            $stmt = $pdo->prepare("
                SELECT 
                    e.*,
                    COALESCE(
                        (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id), 
                        0
                    ) as registered_volunteers
                FROM events e 
                WHERE e.id = :event_id
            ");
=======
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :event_id");
>>>>>>> master
            $stmt->execute([':event_id' => intval($_GET['edit_event'])]);
            $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = "Error fetching event details: " . $e->getMessage();
<<<<<<< HEAD
            error_log("Event Edit Fetch Error: " . $e->getMessage());
        }
    }

    // Fetch events 
    try {
        $events_query = "
            SELECT 
                e.*,
                COALESCE(
                    (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id), 
                    0
                ) as registered_volunteers
            FROM events e
            ORDER BY e.event_date DESC
        ";
        $events_stmt = $pdo->query($events_query);
        $events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
=======
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

>>>>>>> master
    } catch (PDOException $e) {
        $errors[] = "Error fetching events: " . $e->getMessage();
        error_log("Events Fetch Error: " . $e->getMessage());
        $events = [];
<<<<<<< HEAD
    }
=======
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

>>>>>>> master
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
<<<<<<< HEAD
=======
            margin-bottom: 20px;
>>>>>>> master
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
<<<<<<< HEAD
=======
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
>>>>>>> master
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-12 px-md-4">
<<<<<<< HEAD
                <!-- Create/Edit Event Section -->
                <section id="event-form" class="my-4">
                    <div class="card">
                        <div class="card-header">
                            <h3><?php echo $edit_event ? 'Edit Event' : 'Create New Event'; ?></h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" enctype="multipart/form-data">
                                <?php if ($edit_event): ?>
                                    <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
                                <?php endif; ?>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="event_name" class="form-label">Event Name</label>
                                        <input type="text" class="form-control" id="event_name" name="event_name" 
                                               value="<?php echo $edit_event ? htmlspecialchars($edit_event['title']) : ''; ?>" 
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="event_image" class="form-label">Event Image</label>
                                        <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*">
                                        <?php if ($edit_event && !empty($edit_event['image_path'])): ?>
                                            <div class="mt-2">
                                                <img src="<?php echo htmlspecialchars($edit_event['image_path']); ?>" 
                                                     alt="Current event image" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="event_date" class="form-label">Event Date</label>
                                        <input type="date" class="form-control" id="event_date" name="event_date" 
                                               value="<?php echo $edit_event ? $edit_event['event_date'] : ''; ?>" 
                                               min="<?php echo $current_date; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="event_time" class="form-label">Event Time</label>
                                        <input type="time" class="form-control" id="event_time" name="event_time" 
                                               value="<?php echo $edit_event ? $edit_event['event_time'] : ''; ?>" 
                                               required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="event_cause" class="form-label">Event Cause</label>
                                        <select class="form-select" id="event_cause" name="event_cause" required>
                                            <option value="">Select Event Cause</option>
                                            <option value="Educational Support" <?php echo ($edit_event && $edit_event['cause'] == 'Educational Support') ? 'selected' : ''; ?>>Educational Support</option>
                                            <option value="Orphan Care" <?php echo ($edit_event && $edit_event['cause'] == 'Orphan Care') ? 'selected' : ''; ?>>Orphan Care</option>
                                            <option value="Elder Support" <?php echo ($edit_event && $edit_event['cause'] == 'Elder Support') ? 'selected' : ''; ?>>Elder Support</option>
                                            </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="event_description" class="form-label">Event Description</label>
                                        <textarea class="form-control" id="event_description" name="event_description" 
                                                  rows="3" required><?php echo $edit_event ? htmlspecialchars($edit_event['description']) : ''; ?></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <select class="form-select" id="location" name="location" required>
                                            <option value="">Select District</option>
                                            <?php foreach ($districts as $district): ?>
                                                <option value="<?php echo $district; ?>" 
                                                        <?php echo ($edit_event && $edit_event['location'] == $district) ? 'selected' : ''; ?>>
                                                    <?php echo $district; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="max_volunteers" class="form-label">Max Volunteers</label>
                                        <input type="number" class="form-control" id="max_volunteers" name="max_volunteers" 
                                               value="<?php echo $edit_event ? intval($edit_event['max_volunteers'] ?? 1) : ''; ?>" 
                                               min="1" required>
                                    </div>
                                </div>

                                <?php if ($edit_event): ?>
                                    <div class="mb-3">
                                        <label for="event_status" class="form-label">Event Status</label>
                                        <select class="form-control" id="event_status" name="event_status">
                                            <?php 
                                            $current_status = $edit_event ? ($edit_event['status'] ?? 'upcoming') : 'upcoming';
                                            $statuses = ['upcoming', 'ongoing', 'completed', 'cancelled'];
                                            foreach ($statuses as $status):
                                            ?>
                                                <option value="<?php echo $status; ?>" 
                                                        <?php echo ($current_status == $status) ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($status); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-grid">
                                    <button type="submit" name="<?php echo $edit_event ? 'update_event' : 'create_event'; ?>" 
                                            class="btn btn-primary">
                                        <?php echo $edit_event ? 'Update Event' : 'Create Event'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Manage Events Section -->
                <section id="manage-events" class="my-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Existing Events</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($events)): ?>
                                <p>No events have been created yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Cause</th>
                                                <th>Location</th>
                                                <th>Volunteers</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                                <th>View Volunteers</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($events as $event): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($event['title'] ?? 'Untitled Event'); ?></td>
                                                    <td><?php echo date('d M Y', strtotime($event['event_date'] ?? 'now')); ?></td>
                                                    <td><?php echo htmlspecialchars($event['event_time'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($event['cause'] ?? 'Unspecified'); ?></td>
                                                    <td><?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></td>
                                                    <td><?php 
                                                        $registered = $event['registered_volunteers'] ?? 0;
                                                        $total_slots = $event['max_volunteers'] ?? 50;
                                                        echo htmlspecialchars("{$registered} / {$total_slots}"); 
                                                    ?></td>
                                                    <td><?php echo ucfirst(htmlspecialchars($event['status'] ?? 'unknown')); ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="admin_events.php?edit_event=<?php echo $event['id'] ?? ''; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                            <a href="admin_events.php?delete_event=<?php echo $event['id'] ?? ''; ?>" 
                                                               class="btn btn-sm btn-danger" 
                                                               onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        // Only show View Volunteers button if there are registered volunteers
                                                        $registered_count = $event['registered_volunteers'] ?? 0;
                                                        if ($registered_count > 0): 
                                                        ?>
                                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" 
                                                                data-bs-target="#volunteersModal<?php echo $event['id'] ?? ''; ?>">
                                                            View Volunteers 
                                                            <span class="badge bg-light text-dark"><?php echo $registered_count; ?></span>
                                                        </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>

                                                <!-- Volunteers Modal -->
                                                <div class="modal fade" id="volunteersModal<?php echo $event['id'] ?? ''; ?>" tabindex="-1" aria-labelledby="volunteersModalLabel<?php echo $event['id'] ?? ''; ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="volunteersModalLabel<?php echo $event['id'] ?? ''; ?>">
                                                                    Volunteers for <?php echo htmlspecialchars($event['title'] ?? 'Untitled Event'); ?>
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body" id="volunteersModalBody<?php echo $event['id'] ?? ''; ?>">
                                                                <!-- Volunteer details will be dynamically loaded here -->
                                                                <div class="text-center">
                                                                    <button onclick="loadVolunteers(<?php echo $event['id'] ?? ''; ?>)" class="btn btn-primary">
                                                                        View Registered Participants
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Add this script to dynamically load volunteers -->
                                                <script>
                                                function loadVolunteers(eventId) {
                                                    // Show loading spinner
                                                    const modalBody = document.getElementById('volunteersModalBody' + eventId);
                                                    modalBody.innerHTML = `
                                                        <div class="text-center">
                                                            <div class="spinner-border text-primary" role="status">
                                                                <span class="visually-hidden">Loading...</span>
                                                            </div>
                                                            <p>Loading participants...</p>
                                                        </div>
                                                    `;

                                                    // AJAX request to fetch volunteers
                                                    fetch('get_event_volunteers.php?event_id=' + eventId)
                                                        .then(response => response.text())
                                                        .then(html => {
                                                            modalBody.innerHTML = html;
                                                        })
                                                        .catch(error => {
                                                            modalBody.innerHTML = `
                                                                <div class="alert alert-danger text-center" role="alert">
                                                                    Failed to load participants. Please try again.
                                                                </div>
                                                            `;
                                                            console.error('Error:', error);
                                                        });
                                                }
                                                </script>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
=======
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
>>>>>>> master
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
<<<<<<< HEAD

    <script>
        // Disable past dates in the date picker
        window.addEventListener('load', function() {
            const dateInput = document.getElementById('event_date');
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
            
            // If there's a past date already set (in edit mode), allow it
            const currentValue = dateInput.value;
            if (currentValue && currentValue < today) {
                dateInput.setAttribute('min', currentValue);
            }
        });

        // Validate form before submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const dateInput = document.getElementById('event_date');
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate < today) {
                e.preventDefault();
                alert('Please select a future date for the event.');
                dateInput.focus();
            }
        });
    </script>
=======
>>>>>>> master
</body>
</html>
