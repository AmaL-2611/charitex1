<?php
session_start();
require_once 'connect.php';
require_once 'config.php';

// Ensure only volunteers can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'volunteer') {
    header('Location: login.php');
    exit();
}

// Initialize variables
$errors = [];
$success = '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch volunteer details
    $stmt = $pdo->prepare("SELECT * FROM volunteers WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $volunteer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Profile Image Upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (in_array($_FILES['profile_photo']['type'], $allowed_types) && 
                $_FILES['profile_photo']['size'] <= $max_size) {
                
                // Create uploads directory if it doesn't exist
                $upload_dir = 'uploads/profile_photos/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Generate unique filename
                $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $new_filename = 'volunteer_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;

                // Move uploaded file
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
                    // Update profile photo in database
                    $update_photo_stmt = $pdo->prepare("UPDATE volunteers SET profile = ? WHERE id = ?");
                    $update_photo_stmt->execute([$upload_path, $_SESSION['user_id']]);
                    
                    $success = "Profile photo updated successfully!";
                } else {
                    $errors[] = "Failed to upload profile photo.";
                }
            } else {
                $errors[] = "Invalid file type or size. Please upload a JPEG, PNG, or GIF under 5MB.";
            }
        }

        // Password Change
        if (isset($_POST['change_password'])) {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Verify current password
            $verify_stmt = $pdo->prepare("SELECT password FROM volunteers WHERE id = ?");
            $verify_stmt->execute([$_SESSION['user_id']]);
            $user = $verify_stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($current_password, $user['password'])) {
                $errors[] = "Current password is incorrect.";
            } elseif (strlen($new_password) < 8) {
                $errors[] = "New password must be at least 8 characters long.";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "New passwords do not match.";
            } else {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $update_pass_stmt = $pdo->prepare("UPDATE volunteers SET password = ? WHERE id = ?");
                $update_pass_stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $success = "Password updated successfully!";
            }
        }

        // Refresh volunteer data after updates
        $stmt = $pdo->prepare("SELECT * FROM volunteers WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $volunteer = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch upcoming events
    try {
        $events_stmt = $pdo->query("SELECT 
            id, 
            title, 
            description, 
            event_date, 
            location, 
            max_volunteers, 
            current_volunteers, 
            status,
            cause,
            event_time
            FROM events 
            WHERE status = 'upcoming' 
            AND event_date >= CURDATE()
            ORDER BY event_date ASC
        ");
        $events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Default event image handling
        $default_event_image = 'uploads/event_images/default_event.jpg';
    } catch (PDOException $e) {
        $events = [];
        $default_event_image = 'uploads/event_images/default_event.jpg';
        error_log("Error fetching events: " . $e->getMessage());
    }

    // Fetch available events to join
    try {
        // Get all column names to help with debugging
        $columns_stmt = $pdo->query("SHOW COLUMNS FROM events");
        $columns = $columns_stmt->fetchAll(PDO::FETCH_COLUMN);
        error_log("Available columns: " . implode(', ', $columns));

        // Prepare a flexible query
        $query_parts = [
            'select' => [],
            'where' => []
        ];

        // Dynamically select columns
        $column_mapping = [
            'id' => ['id', 'event_id'],
            'title' => ['title', 'name', 'event_name'],
            'description' => ['description', 'event_description'],
            'location' => ['location', 'event_location'],
            'date' => ['event_date', 'date'],
            'status' => ['status', 'event_status'],
            'volunteers' => [
                'current_volunteers', 
                'max_volunteers'
            ]
        ];

        // Find matching columns
        $matched_columns = [];
        foreach ($column_mapping as $key => $possible_columns) {
            foreach ($possible_columns as $col) {
                if (in_array($col, $columns)) {
                    $matched_columns[$key] = $col;
                    break;
                }
            }
        }

        // Construct query
        $query = "SELECT ";
        $select_columns = [];
        $where_conditions = [];

        // Add ID column
        if (isset($matched_columns['id'])) {
            $select_columns[] = $matched_columns['id'] . " AS id";
        }

        // Add title column
        if (isset($matched_columns['title'])) {
            $select_columns[] = $matched_columns['title'] . " AS title";
        }

        // Add description column
        if (isset($matched_columns['description'])) {
            $select_columns[] = $matched_columns['description'] . " AS description";
        }

        // Add location column
        if (isset($matched_columns['location'])) {
            $select_columns[] = $matched_columns['location'] . " AS location";
        }

        // Add date column
        if (isset($matched_columns['date'])) {
            $select_columns[] = $matched_columns['date'] . " AS event_date";
            $where_conditions[] = $matched_columns['date'] . " >= CURDATE()";
        }

        // Add status column
        if (isset($matched_columns['status'])) {
            $select_columns[] = $matched_columns['status'] . " AS status";
            $where_conditions[] = $matched_columns['status'] . " = 'upcoming'";
        }

        // Handle volunteer slots
        $volunteer_column = null;
        foreach ($column_mapping['volunteers'] as $vol_col) {
            if (in_array($vol_col, $columns)) {
                $select_columns[] = "$vol_col AS current_volunteers";
                
                // If max volunteers column exists, add condition
                $max_vol_col = $vol_col === 'current_volunteers' ? 'max_volunteers' : 'total_volunteer_slots';
                if (in_array($max_vol_col, $columns)) {
                    $where_conditions[] = "$vol_col < $max_vol_col";
                }
                break;
            }
        }

        // Finalize query
        $query .= implode(", ", $select_columns) . 
                  " FROM events " . 
                  (empty($where_conditions) ? "" : " WHERE " . implode(" AND ", $where_conditions)) . 
                  " ORDER BY event_date ASC";

        // Log the generated query
        error_log("Generated Events Query: " . $query);

        // Prepare and execute query
        $available_events_stmt = $pdo->prepare($query);
        $available_events_stmt->execute();
        $available_events = $available_events_stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Comprehensive error logging
        error_log("Events Query Error: " . $e->getMessage());
        error_log("Full Error Details: " . print_r($e, true));
        
        $available_events = []; 
        $events_error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        // Catch any other unexpected errors
        error_log("Unexpected Error in events query: " . $e->getMessage());
        
        $available_events = []; 
        $events_error = "Unexpected error: " . $e->getMessage();
    }

    // Fetch registered events for this volunteer
    $registered_events_stmt = $pdo->prepare("
        SELECT 
            e.id, 
            e.title, 
            e.description, 
            e.event_date, 
            e.event_time,
            e.location, 
            e.max_volunteers, 
            e.current_volunteers,
            e.status,
            e.cause
        FROM events e
        JOIN event_registrations er ON e.id = er.event_id
        WHERE er.volunteer_id = ? AND e.event_date >= CURDATE()
        ORDER BY e.event_date ASC
    ");
    $registered_events_stmt->execute([$_SESSION['user_id']]);
    $registered_events = $registered_events_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard - ChariteX</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }
        .event-card {
            transition: transform 0.3s ease-in-out;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .event-card:hover {
            transform: scale(1.05);
        }
        .event-image {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="update_profile.php">Update Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#events">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#hours">Log Hours</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#certificates">Certificates</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Volunteer Profile Section -->
                <section id="dashboard" class="my-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card dashboard-card">
                                <div class="card-body text-center">
                                    <img src="<?php echo htmlspecialchars($volunteer['profile'] ?? 'default_profile.png'); ?>" 
                                         alt="Volunteer Profile" class="profile-img mb-3">
                                    <h4><?php echo htmlspecialchars($volunteer['name']); ?></h4>
                                    
                                    <p>Total Volunteer Hours: <?php echo $volunteer['total_hours'] ?? 0; ?></p>
                                </div>
                            </div>
                        </div>

                </section>

                <!-- Profile Editing Section -->
                
                <!-- Upcoming Events Section -->
                <section class="upcoming-events mt-4">
                    <div class="container">
                        <h2 class="mb-3">Upcoming Events</h2>
                        <?php if (!empty($events_error)): ?>
                            <div class="alert alert-danger"><?php echo $events_error; ?></div>
                        <?php elseif (empty($events)): ?>
                            <div class="alert alert-info">No upcoming events at the moment.</div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($events as $event): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card event-card">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                                <p class="card-text"><?php echo htmlspecialchars($event['description'] ?? 'No description'); ?></p>
                                                <ul class="list-unstyled">
                                                    <li><strong>Date:</strong> <?php echo date('d M Y', strtotime($event['event_date'])); ?></li>
                                                    <li><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></li>
                                                    <li><strong>Volunteers:</strong> <?php 
                                                        // Log the entire event array for debugging
                                                        error_log("Event details: " . print_r($event, true));
                                                        
                                                        // Fallback to safe defaults
                                                        $current_volunteers = $event['current_volunteers'] ?? 0;
                                                        $total_volunteers = $event['max_volunteers'] ?? 50;
                                                        echo "{$current_volunteers} / {$total_volunteers}"; 
                                                    ?></li>
                                                </ul>
                                                <?php if ($current_volunteers < $total_volunteers): ?>
                                                    <a href="event_registration.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary">Register</a>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Event Full</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Events Section -->
                <section id="events" class="my-4">
                    <div class="row">
                        <!-- Available Events -->
                        <div class="col-md-6">
                            <div class="card dashboard-card">
                                <div class="card-header">Join New Events</div>
                                <div class="card-body">
                                    <?php if (empty($available_events)): ?>
                                        <p>No new events available.</p>
                                    <?php else: ?>
                                        <ul class="list-group">
                                            <?php foreach ($available_events as $event): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <?php echo htmlspecialchars($event['title']); ?>
                                                    <form method="POST" action="event_registration.php">
                                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-primary">Register</button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Registered Events -->
                        <div class="col-md-12">
                            <div class="card dashboard-card">
                                <div class="card-header">Registered Events</div>
                                <div class="card-body">
                                    <?php if (empty($registered_events)): ?>
                                        <p>No registered events.</p>
                                    <?php else: ?>
                                        <div class="row row-cols-1 row-cols-md-3 g-4">
                                            <?php foreach ($registered_events as $event): ?>
                                                <div class="col">
                                                    <div class="card h-100 event-card">
                                                        <?php 
                                                        // Use default image if no event image is available
                                                        $event_image = $default_event_image; 
                                                        ?>
                                                        <img src="<?php echo htmlspecialchars($event_image); ?>" 
                                                             class="card-img-top event-image" 
                                                             alt="<?php echo htmlspecialchars($event['title']); ?>">
                                                        <div class="card-body">
                                                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                                            <p class="card-text"><?php echo htmlspecialchars($event['description'] ?? 'No description available'); ?></p>
                                                            <ul class="list-unstyled">
                                                                <li><strong>Location:</strong> <?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></li>
                                                                <li><strong>Date:</strong> <?php echo date('d M Y', strtotime($event['event_date'])); ?></li>
                                                                <li><strong>Time:</strong> <?php echo date('h:i A', strtotime($event['event_time'] ?? 'now')); ?></li>
                                                                <li><strong>Volunteers:</strong> <?php 
                                                                    $current_volunteers = $event['current_volunteers'] ?? 0;
                                                                    $total_volunteers = $event['max_volunteers'] ?? 50;
                                                                    echo "{$current_volunteers} / {$total_volunteers}"; 
                                                                ?></li>
                                                            </ul>
                                                        </div>
                                                        <div class="card-footer">
                                                            <form method="post" action="event_unregistration.php">
                                                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm">Unregister</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Log Hours & Certificates Section -->
                <section id="hours" class="my-4">
                    <div class="row">
                        <!-- Log Volunteer Hours -->
                        <div class="col-md-6">
                            <div class="card dashboard-card">
                                <div class="card-header">Log Volunteer Hours</div>
                                <div class="card-body">
                                    <form method="POST" action="log_hours.php">
                                        <div class="mb-3">
                                            <label for="event" class="form-label">Select Event</label>
                                            <select name="event_id" class="form-select" required>
                                                <option value="">Choose an event</option>
                                                <?php foreach ($registered_events as $event): ?>
                                                    <option value="<?php echo $event['id']; ?>">
                                                        <?php echo htmlspecialchars($event['title']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="hours" class="form-label">Hours Worked</label>
                                            <input type="number" name="hours" class="form-control" min="0.5" max="24" step="0.5" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Log Hours</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Certificates -->
                        <div class="col-md-6">
                            <div class="card dashboard-card">
                                <div class="card-header">Participation Certificates</div>
                                <div class="card-body">
                                    <form method="POST" action="participation_certificates.php">
                                        <div class="mb-3">
                                            <label for="event" class="form-label">Select Completed Event</label>
                                            <select name="event_id" class="form-select" required>
                                                <option value="">Choose an event</option>
                                                <?php foreach ($registered_events as $event): ?>
                                                    <option value="<?php echo $event['id']; ?>">
                                                        <?php echo htmlspecialchars($event['title']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success">Request Certificate</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
