<?php
session_start();
require_once 'connect.php';
require_once 'config.php';

// Ensure only volunteers can register for events
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'volunteer') {
    header("Location: login.php");
    exit();
}

$errors = [];
$success = '';

// Fetch upcoming events
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get columns in the events table
    $columns_stmt = $pdo->query("SHOW COLUMNS FROM events");
    $columns = $columns_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Define column mappings with fallback options
    $column_mappings = [
        'title' => ['title', 'name', 'event_name'],
        'location' => ['location', 'venue', 'event_location'],
        'event_date' => ['event_date', 'date', 'start_date'],
        'event_time' => ['event_time', 'time', 'start_time'],
        'cause' => ['cause', 'category', 'event_type'],
        'description' => ['description', 'details', 'event_description']
    ];

    // Determine which columns to use for volunteers and availability
    $volunteer_column = null;
    $max_volunteer_column = null;
    $availability_column = null;

    // Check for various volunteer-related columns
    $volunteer_options = [
        'current_volunteers',
        'total_volunteer_slots',
        'volunteers'
    ];

    $max_volunteer_options = [
        'max_volunteers',
        'total_volunteer_slots'
    ];

    $availability_options = [
        'available_slots',
        'remaining_slots'
    ];

    // Find first matching column
    foreach ($volunteer_options as $col) {
        if (in_array($col, $columns)) {
            $volunteer_column = $col;
            break;
        }
    }

    foreach ($max_volunteer_options as $col) {
        if (in_array($col, $columns)) {
            $max_volunteer_column = $col;
            break;
        }
    }

    foreach ($availability_options as $col) {
        if (in_array($col, $columns)) {
            $availability_column = $col;
            break;
        }
    }

    // Construct dynamic query
    $query = "SELECT * FROM events WHERE status = 'upcoming'";
    
    // Add availability condition if column exists
    if ($availability_column) {
        $query .= " AND $availability_column > 0";
    } elseif ($volunteer_column && $max_volunteer_column) {
        $query .= " AND $volunteer_column < $max_volunteer_column";
    }
    
    $query .= " ORDER BY event_date ASC";

    // Prepare and execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log the generated query for debugging
    error_log("Generated Events Query: " . $query);
    error_log("Columns used - Volunteer: " . ($volunteer_column ?? 'N/A') . 
              ", Max Volunteers: " . ($max_volunteer_column ?? 'N/A') . 
              ", Availability: " . ($availability_column ?? 'N/A'));

} catch (PDOException $e) {
    $errors[] = "Error fetching events: " . $e->getMessage();
    error_log("Event Fetch Error: " . $e->getMessage());
    $upcoming_events = []; // Ensure $upcoming_events is always an array
}

// Function to safely get a column value with multiple fallback options
function safe_get_column($event, $column_mappings, $key, $default = 'N/A') {
    foreach ($column_mappings[$key] as $possible_column) {
        if (isset($event[$possible_column])) {
            return $event[$possible_column];
        }
    }
    return $default;
}

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    $volunteer_id = $_SESSION['user_id'];

    try {
        // Construct dynamic check query
        $event_check_query = "SELECT * FROM events WHERE id = ? AND event_date >= CURDATE()";
        
        // Add availability check based on detected columns
        if ($availability_column) {
            $event_check_query .= " AND $availability_column > 0";
        } elseif ($volunteer_column && $max_volunteer_column) {
            $event_check_query .= " AND $volunteer_column < $max_volunteer_column";
        }

        // Check if event exists and has space
        $event_stmt = $pdo->prepare($event_check_query);
        $event_stmt->execute([$event_id]);
        $event = $event_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            $errors[] = "Event is not available for registration.";
        } else {
<<<<<<< HEAD
            // Determine max volunteers column dynamically
            $max_volunteer_column = 'max_volunteers';
            
            // Get current number of volunteers for this event
            $current_volunteers_stmt = $pdo->prepare("
                SELECT current_volunteers 
                FROM events 
                WHERE id = ?
            ");
            $current_volunteers_stmt->execute([$event_id]);
            $event_data = $current_volunteers_stmt->fetch(PDO::FETCH_ASSOC);
            
            $current_volunteers = $event_data['current_volunteers'] ?? 0;
            $max_volunteers = $event['max_volunteers'] ?? 50;
            
            // Check if event is full
            if ($current_volunteers >= $max_volunteers) {
                $errors[] = "Sorry, this event is already full.";
                // Redirect or handle the error
                header("Location: volunteer.php?error=event_full");
                exit();
            }
            
            // Update event with increased volunteer count
            $update_event_stmt = $pdo->prepare("
                UPDATE events 
                SET current_volunteers = current_volunteers + 1 
                WHERE id = ? AND current_volunteers < max_volunteers
            ");
            $update_result = $update_event_stmt->execute([$event_id]);

=======
>>>>>>> master
            // Check if already registered
            $check_reg_stmt = $pdo->prepare("
                SELECT * FROM event_registrations 
                WHERE event_id = ? AND volunteer_id = ?
            ");
            $check_reg_stmt->execute([$event_id, $volunteer_id]);
            $existing_reg = $check_reg_stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_reg) {
                $errors[] = "You are already registered for this event.";
            } else {
                // Register for event
                $reg_stmt = $pdo->prepare("
                    INSERT INTO event_registrations 
                    (event_id, volunteer_id, status) 
                    VALUES (?, ?, 'registered')
                ");
                $reg_stmt->execute([$event_id, $volunteer_id]);

<<<<<<< HEAD
=======
                // Update event's volunteer count
                $update_queries = [];
                if ($volunteer_column) {
                    $update_queries[] = "UPDATE events SET $volunteer_column = $volunteer_column + 1 WHERE id = ?";
                }
                if ($availability_column) {
                    $update_queries[] = "UPDATE events SET $availability_column = $availability_column - 1 WHERE id = ?";
                }

                foreach ($update_queries as $update_query) {
                    $update_stmt = $pdo->prepare($update_query);
                    $update_stmt->execute([$event_id]);
                }

>>>>>>> master
                // Redirect based on user type
                if (isset($_SESSION['user_type'])) {
                    switch ($_SESSION['user_type']) {
                        case 'admin':
                            header("Location: admin_dashboard.php?success=1");
                            exit();
                        case 'volunteer':
                            header("Location: volunteer_dashboard.php?success=1");
                            exit();
                        case 'donor':
                            header("Location: donor_dashboard.php?success=1");
                            exit();
                        default:
                            header("Location: index.php?success=1");
                            exit();
                    }
                } else {
                    // Fallback redirect
                    header("Location: index.php?success=1");
                    exit();
                }

                $success = "Successfully registered for the event!";
            }
        }
    } catch (PDOException $e) {
        $errors[] = "Registration failed: " . $e->getMessage();
        error_log("Registration Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Registration - CHARITEX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .event-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .register-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .register-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Upcoming Events</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($upcoming_events)): ?>
        <p>No upcoming events at the moment.</p>
    <?php else: ?>
        <?php foreach ($upcoming_events as $event): ?>
            <div class="event-card">
                <div class="event-header">
                    <h2><?php echo htmlspecialchars(safe_get_column($event, $column_mappings, 'title')); ?></h2>
                    <form method="POST">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <button type="submit" class="register-btn" 
                                <?php 
                                // Determine availability
                                $available_slots = 0;
                                if ($availability_column) {
                                    $available_slots = $event[$availability_column] ?? 0;
                                } elseif ($volunteer_column && $max_volunteer_column) {
                                    $current_volunteers = $event[$volunteer_column] ?? 0;
                                    $max_volunteers = $event[$max_volunteer_column] ?? 0;
                                    $available_slots = max(0, $max_volunteers - $current_volunteers);
                                }
                                
                                // Disable button if no slots
                                echo $available_slots <= 0 ? 'disabled' : ''; 
                                ?>>
                            <?php echo $available_slots > 0 ? 'Register' : 'Full'; ?>
                        </button>
                    </form>
                </div>
                <p><strong>Location:</strong> <?php echo htmlspecialchars(safe_get_column($event, $column_mappings, 'location')); ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime(safe_get_column($event, $column_mappings, 'event_date'))); ?></p>
                <p><strong>Time:</strong> <?php echo date('h:i A', strtotime(safe_get_column($event, $column_mappings, 'event_time'))); ?></p>
                <p><strong>Cause:</strong> <?php echo htmlspecialchars(safe_get_column($event, $column_mappings, 'cause')); ?></p>
                <p><strong>Available Slots:</strong> <?php 
                // Determine and display available slots
                $available_slots = 0;
                if ($availability_column) {
                    $available_slots = $event[$availability_column] ?? 0;
                } elseif ($volunteer_column && $max_volunteer_column) {
                    $current_volunteers = $event[$volunteer_column] ?? 0;
                    $max_volunteers = $event[$max_volunteer_column] ?? 0;
                    $available_slots = max(0, $max_volunteers - $current_volunteers);
                }
                echo $available_slots; 
                ?></p>
                <p><?php echo htmlspecialchars(safe_get_column($event, $column_mappings, 'description')); ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
