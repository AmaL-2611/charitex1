<!-- <?php
// require_once 'config.php';

// header('Content-Type: application/json');

// if (isset($_GET['email'])) {
//     $email = $_GET['email'];
    
//     // Create database connection
//     $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
//     // Check in donors table
//     $check_donor = $pdo->prepare("SELECT email FROM donors WHERE email = ?");
//     $check_donor->execute([$email]);
//     $donor_exists = $check_donor->rowCount() > 0;
    
//     // Check in volunteers table
//     $check_volunteer = $pdo->prepare("SELECT email FROM volunteers WHERE email = ?");
//     $check_volunteer->execute([$email]);
//     $volunteer_exists = $check_volunteer->rowCount() > 0;
    
//     echo json_encode(['exists' => ($donor_exists || $volunteer_exists)]);
// } else {
//     echo json_encode(['error' => 'No email provided']);
// }
?> -->
