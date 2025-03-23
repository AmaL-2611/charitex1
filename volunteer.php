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
<<<<<<< HEAD
=======
$events = [];
$volunteer = null; // Initialize volunteer variable
>>>>>>> master

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch volunteer details
<<<<<<< HEAD
    $stmt = $pdo->prepare("SELECT * FROM volunteers WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $volunteer = $stmt->fetch(PDO::FETCH_ASSOC);
=======
    $volunteer_stmt = $pdo->prepare("SELECT * FROM volunteers WHERE id = ?");
    $volunteer_stmt->execute([$_SESSION['user_id']]);
    $volunteer = $volunteer_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$volunteer) {
        error_log("Volunteer not found for ID: " . $_SESSION['user_id']);
        header('location:login.php');
        exit();
    }

    // Fetch approved events
    $query = "
        SELECT e.*, o.name as orphanage_name,
               COALESCE((
                   SELECT COUNT(*) 
                   FROM event_registrations 
                   WHERE event_id = e.id
               ), 0) as registered_count
        FROM events e
        LEFT JOIN orphanage o ON e.created_by = o.id
        WHERE e.approval_status = 'approved'
        AND e.event_date >= CURDATE()
        ORDER BY e.event_date ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
>>>>>>> master

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

<<<<<<< HEAD
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

=======
    // Handle event registration
    if (isset($_POST['register_event'])) {
        $event_id = intval($_POST['event_id']);
        
        // Check if already registered
        $check_stmt = $pdo->prepare("
            SELECT * FROM event_registrations 
            WHERE event_id = ? AND volunteer_id = ?
        ");
        $check_stmt->execute([$event_id, $_SESSION['user_id']]);
        
        if ($check_stmt->rowCount() > 0) {
            $errors[] = "You are already registered for this event.";
        } else {
            // Register for the event
            $register_stmt = $pdo->prepare("
                INSERT INTO event_registrations (event_id, volunteer_id, user_type)
                VALUES (?, ?, 'volunteer')
            ");
            if ($register_stmt->execute([$event_id, $_SESSION['user_id']])) {
                $success = "Successfully registered for the event!";
            } else {
                $errors[] = "Failed to register for the event.";
            }
        }
    }

    // Fetch upcoming events
    $upcoming_events_stmt = $pdo->prepare("
        SELECT * FROM events 
        WHERE status = 'upcoming' 
        AND event_date >= CURDATE()
        ORDER BY event_date ASC
    ");
    $upcoming_events_stmt->execute();
    $upcoming_events = $upcoming_events_stmt->fetchAll(PDO::FETCH_ASSOC);

>>>>>>> master
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
<<<<<<< HEAD
        $column_mapping = [
            'id' => ['id', 'event_id'],
            'title' => ['title', 'name', 'event_name'],
=======
        $column_mapping = [ 'id' => ['id', 'event_id'],
            'name' => ['name', 'title', 'event_name'],
>>>>>>> master
            'description' => ['description', 'event_description'],
            'location' => ['location', 'event_location'],
            'date' => ['event_date', 'date'],
            'status' => ['status', 'event_status'],
            'volunteers' => [
                'current_volunteers', 
<<<<<<< HEAD
                'max_volunteers'
=======
                'max_volunteers', 
                'total_volunteer_slots'
>>>>>>> master
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

<<<<<<< HEAD
        // Add title column
        if (isset($matched_columns['title'])) {
            $select_columns[] = $matched_columns['title'] . " AS title";
=======
        // Add name column
        if (isset($matched_columns['name'])) {
            $select_columns[] = $matched_columns['name'] . " AS name";
>>>>>>> master
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

<<<<<<< HEAD
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
=======
    // Fetch upcoming events
    try {
        $events_stmt = $pdo->query("SELECT id, name, description, event_date, location, 
                   max_volunteers, current_volunteers, status 
            FROM events 
            WHERE status = 'upcoming' 
            AND event_date >= CURDATE()
            AND id IN (
                SELECT DISTINCT id FROM events
            )
            ORDER BY event_date ASC
        ");
        $upcoming_events_list = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $events_error = "Could not fetch events: " . $e->getMessage();
    }

    // Fetch registered events for this volunteer
    $registered_events_stmt = $pdo->prepare("
        SELECT e.* FROM events e
>>>>>>> master
        JOIN event_registrations er ON e.id = er.event_id
        WHERE er.volunteer_id = ? AND e.event_date >= CURDATE()
        ORDER BY e.event_date ASC
    ");
    $registered_events_stmt->execute([$_SESSION['user_id']]);
    $registered_events = $registered_events_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
<<<<<<< HEAD
    die("Database error: " . $e->getMessage());
}
=======
    $errors[] = "Database error: " . $e->getMessage();
    error_log("Volunteer Dashboard Error: " . $e->getMessage());
}

// Debug: Print the SQL query
// echo "<div class='container'>";
// echo "<div class='alert alert-secondary'>";
// echo "<h4>SQL Query:</h4>";
// echo "<pre>" . $query . "</pre>";
// echo "</div>";
// echo "</div>";
>>>>>>> master
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard - ChariteX</title>
<<<<<<< HEAD
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
=======
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2a6c;
            --secondary: #b21f1f;
            --accent: #fdbb2d;
            --dark-bg: #121212;
            --dark-card: #1e1e1e;
            --transition: all 0.3s ease;
        }

        body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        background-image: url("n3.jpg");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        position: relative;
      }

      body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: -1;
      }
        /* Sidebar Styling */
        .sidebar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
        }

        /* Main Content Adjustment */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #333;
            transition: var(--transition);
            border-radius: 8px;
            margin: 5px 0;
        }

        .nav-link:hover, .nav-link.active {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            transform: translateX(5px);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Profile Section */
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid var(--primary);
            box-shadow: 0 0 20px rgba(26, 42, 108, 0.3);
            transition: var(--transition);
        }

        .profile-img:hover {
            transform: scale(1.05);
            border-color: var(--secondary);
        }

        /* Dashboard Cards */
        .dashboard-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            border: none;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        /* Event Cards */
        .event-card {
            border-radius: 15px;
            overflow: hidden;
            transition: var(--transition);
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .event-info {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .event-info i {
            color: var(--primary);
        }

        /* Progress Bars */
        .progress {
            height: 10px;
            border-radius: 5px;
            background: #e9ecef;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            transition: width 1s ease-in-out;
        }

        /* Buttons */
        .btn-gradient {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            transition: var(--transition);
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 42, 108, 0.3);
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background: var(--dark-bg);
            color: white;
        }

        .dark-mode .sidebar {
            background: rgba(30, 30, 30, 0.9);
        }

        .dark-mode .dashboard-card {
            background: var(--dark-card);
            color: white;
        }

        .dark-mode .nav-link {
            color: white;
        }

        .dark-mode .card-body {
            color: white;
        }

        .dark-mode h1, 
        .dark-mode h2, 
        .dark-mode h3, 
        .dark-mode h4, 
        .dark-mode h5, 
        .dark-mode h6,
        .dark-mode p,
        .dark-mode .volunteer-stats,
        .dark-mode .event-info,
        .dark-mode .card-title,
        .dark-mode .card-text {
            color: white;
        }

        .dark-mode .event-info i {
            color: var(--accent);
        }

        .dark-mode .progress {
            background: rgba(255, 255, 255, 0.1);
        }

        .dark-mode .sidebar-toggle {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .dark-mode .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            z-index: 1000;
        }

        .theme-toggle:hover {
            transform: rotate(180deg);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
                transition: all 0.3s ease;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }

        /* Hamburger Menu Button */
        .sidebar-toggle {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1050;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        /* Move button to right when sidebar is active */
        .sidebar-toggle.active {
            left: auto;
            right: 20px;
        }

        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Sidebar Transition */
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        /* Adjust main content */
        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        .main-content.sidebar-active {
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .main-content.sidebar-active {
                margin-left: 0;
            }
>>>>>>> master
        }
    </style>
</head>
<body>
<<<<<<< HEAD
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
=======
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Hamburger Menu Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container-fluid p-0">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="px-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="update_profile.php">
                            <i class="fas fa-user-edit"></i> Update Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#events">
                            <i class="fas fa-calendar-alt"></i> Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#hours">
                            <i class="fas fa-clock"></i> Log Hours
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#certificates">
                            <i class="fas fa-certificate"></i> Certificates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Adjusted Volunteer Profile Section -->
            <section id="dashboard" class="mt-5 d-flex justify-content-center">
                <div class="row w-100 justify-content-center">
                    <div class="col-md-4">  <!-- Reduced from col-md-6 to col-md-4 -->
                        <div class="card dashboard-card text-center">
                            <div class="card-body py-4">  <!-- Added padding-y -->
                                <img src="<?php echo htmlspecialchars($volunteer['profile'] ?? 'default_profile.png'); ?>" 
                                     alt="Volunteer Profile" 
                                     class="profile-img mb-3">
                                <h4 class="mb-2"><?php echo htmlspecialchars($volunteer['name']); ?></h4>  <!-- Changed from h3 to h4 -->
                                
                                <div class="volunteer-stats mb-3">  <!-- Reduced margin-bottom -->
                                    <p class="mb-1 small">  <!-- Added small class and reduced margin -->
                                        <i class="fas fa-clock me-2"></i>
                                        Total Hours: <strong><?php echo $volunteer['total_hours'] ?? 0; ?></strong>
                                    </p>
                                    <p class="mb-1 small">
                                        <i class="fas fa-envelope me-2"></i>
                                        <strong><?php echo htmlspecialchars($volunteer['email']); ?></strong>
                                    </p>
                                    <p class="mb-1 small">
                                        <i class="fas fa-phone me-2"></i>
                                        <strong><?php echo htmlspecialchars($volunteer['phone'] ?? 'Not provided'); ?></strong>
                                    </p>
                                </div>

                                <div class="d-grid">
                                    <a href="update_profile.php" class="btn btn-gradient btn-sm">  <!-- Added btn-sm -->
                                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Upcoming Events Section -->
            <section class="upcoming-events mt-4">
                <div class="container">
                    <h2 class="mb-3"><font color="white">Upcoming Events</font></h2>
                    <?php if (!empty($events_error)): ?>
                        <div class="alert alert-danger"><?php echo $events_error; ?></div>
                    <?php elseif (empty($upcoming_events_list)): ?>
                        <div class="alert alert-info">No upcoming events at the moment.</div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($upcoming_events_list as $event): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card event-card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars($event['description'] ?? 'No description'); ?></p>
                                            <ul class="list-unstyled">
                                                <li><strong>Date:</strong> <?php echo date('d M Y', strtotime($event['event_date'])); ?></li>
                                                <li><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></li>
                                                <li><strong>Volunteers:</strong> <?php echo $event['current_volunteers'] . ' / ' . $event['max_volunteers']; ?></li>
                                            </ul>
                                            <?php if ($event['current_volunteers'] < $event['max_volunteers']): ?>
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
                    <div class="col-md-12">
                        <div class="card dashboard-card">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">Join New Events</h4>
                            </div>
                            <div class="card-body">
                                <?php if (empty($events)): ?>
                                    <div class="alert alert-info">No approved events available at the moment.</div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($events as $event): ?>
                                            <div class="col-md-4 mb-4">
                                                <div class="card h-100 event-card">
                                                    <div class="card-header">
                                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['name']); ?></h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="mb-3">
                                                            <strong>Organized by:</strong> 
                                                            <?php echo htmlspecialchars($event['orphanage_name']); ?>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Date:</strong> 
                                                            <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Time:</strong> 
                                                            <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Location:</strong> 
                                                            <?php echo htmlspecialchars($event['location']); ?>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Cause:</strong> 
                                                            <?php echo htmlspecialchars($event['cause']); ?>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Description:</strong><br>
                                                            <?php echo htmlspecialchars($event['description']); ?>
                                                        </div>
                                                        
                                                        <?php $spots_left = $event['max_volunteers'] - $event['registered_count']; ?>
                                                        <div class="mb-3">
                                                            <strong>Available Spots:</strong> 
                                                            <span class="<?php echo $spots_left > 0 ? 'text-success' : 'text-danger'; ?>">
                                                                <?php echo $spots_left; ?> of <?php echo $event['max_volunteers']; ?>
                                                            </span>
                                                        </div>

                                                        <?php if ($spots_left > 0): ?>
                                                            <form method="POST" class="mt-auto">
                                                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                                <button type="submit" name="register_event" class="btn btn-primary w-100">
                                                                    Register for Event
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <div class="alert alert-warning mb-0">
                                                                Registration Full
                                                            </div>
                                                        <?php endif; ?>
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
                                                    <?php echo htmlspecialchars($event['name']); ?>
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
                                                    <?php echo htmlspecialchars($event['name']); ?>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const icon = document.querySelector('.theme-toggle i');
            icon.classList.toggle('fa-moon');
            icon.classList.toggle('fa-sun');
        }

        // Add active class to current nav item
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            if(link.href === window.location.href){
                link.classList.add('active');
            }
        });

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleBtn = document.querySelector('.sidebar-toggle');
            const toggleIcon = toggleBtn.querySelector('i');
            
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('sidebar-active');
            toggleBtn.classList.toggle('active');
            
            // Change icon
            toggleIcon.classList.toggle('fa-bars');
            toggleIcon.classList.toggle('fa-times');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 768 && 
                !event.target.closest('.sidebar') && 
                !event.target.closest('.sidebar-toggle') && 
                sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    </script>
>>>>>>> master
</body>
</html>
