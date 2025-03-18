<?php
session_start();
require_once 'connect.php';

// Ensure only admins can access this
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized access";
    exit();
}

// Get event ID from GET parameter
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    echo '<div class="alert alert-danger text-center" role="alert">Invalid event ID</div>';
    exit();
}

try {
    // Dynamically detect column names
    $volunteers_columns = [];
    $columns_stmt = $pdo->query("SHOW COLUMNS FROM volunteers");
    $columns = $columns_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Define possible column mappings
    $column_mappings = [
        'name' => ['name', 'full_name', 'username'],
        'first_name' => ['first_name', 'firstname', 'fname'],
        'last_name' => ['last_name', 'lastname', 'lname'],
        'email' => ['email', 'user_email', 'contact_email'],
        'phone' => ['phone', 'phone_number', 'contact_number'],
        'location' => ['location', 'city', 'address'],
        'skills' => ['skills', 'skill_set', 'volunteer_skills'],
        'availability' => ['availability', 'available_time', 'volunteer_hours']
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

    // Dynamically build select columns
    $select_name = isset($volunteers_columns['first_name']) && isset($volunteers_columns['last_name']) 
        ? "CONCAT(v.{$volunteers_columns['first_name']}, ' ', v.{$volunteers_columns['last_name']})" 
        : (isset($volunteers_columns['name']) ? "v.{$volunteers_columns['name']}" : "'Unknown'");

    // Construct the query with additional details
    $volunteers_query = "
        SELECT 
            " . ($select_name . " AS full_name") . 
            (isset($volunteers_columns['email']) ? ", v.{$volunteers_columns['email']} AS email" : ", 'N/A' AS email") .
            (isset($volunteers_columns['phone']) ? ", v.{$volunteers_columns['phone']} AS phone" : ", 'N/A' AS phone") .
            (isset($volunteers_columns['location']) ? ", v.{$volunteers_columns['location']} AS location" : ", 'N/A' AS location") .
            (isset($volunteers_columns['skills']) ? ", v.{$volunteers_columns['skills']} AS skills" : ", 'N/A' AS skills") .
            (isset($volunteers_columns['availability']) ? ", v.{$volunteers_columns['availability']} AS availability" : ", 'N/A' AS availability") . "
        FROM volunteers v
        JOIN event_registrations er ON v.id = er.volunteer_id
        WHERE er.event_id = ?
    ";

    $volunteers_stmt = $pdo->prepare($volunteers_query);
    $volunteers_stmt->execute([$event_id]);
    $volunteers = $volunteers_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate HTML output
    if (!empty($volunteers)): ?>
        <div class="accordion" id="volunteersAccordion">
            <?php foreach ($volunteers as $index => $volunteer): ?>
                <div class="card">
                    <div class="card-header" id="heading<?php echo $index; ?>">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left" type="button" 
                                    data-toggle="collapse" 
                                    data-target="#collapse<?php echo $index; ?>" 
                                    aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                    aria-controls="collapse<?php echo $index; ?>">
                                <?php echo htmlspecialchars($volunteer['full_name']); ?>
                            </button>
                        </h2>
                    </div>

                    <div id="collapse<?php echo $index; ?>" 
                         class="collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                         aria-labelledby="heading<?php echo $index; ?>" 
                         data-parent="#volunteersAccordion">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Contact Information</h5>
                                    <?php if (isset($volunteers_columns['email'])): ?>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($volunteer['email']); ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($volunteers_columns['phone'])): ?>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($volunteer['phone']); ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($volunteers_columns['location'])): ?>
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($volunteer['location']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <h5>Volunteer Details</h5>
                                    <?php if (isset($volunteers_columns['skills'])): ?>
                                        <p><strong>Skills:</strong> <?php echo htmlspecialchars($volunteer['skills']); ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($volunteers_columns['availability'])): ?>
                                        <p><strong>Availability:</strong> <?php echo htmlspecialchars($volunteer['availability']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-3 text-center">
            <strong>Total Registered Volunteers:</strong> 
            <?php echo count($volunteers); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            No volunteers have registered for this event.
        </div>
    <?php endif;

} catch (PDOException $e) {
    error_log("Volunteers Fetch Error: " . $e->getMessage());
    echo '<div class="alert alert-danger text-center" role="alert">
            Error fetching volunteers. Please try again later.
          </div>';
}
?>
