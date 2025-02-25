<?php
// require_once 'config.php';

// header('Content-Type: application/json');

// if (isset($_GET['mobile'])) {
//     $mobile = $_GET['mobile'];
    
//     // Create database connection
//     $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
//     // Check in donors table
//     $check_donor = $pdo->prepare("SELECT mobile FROM donors WHERE mobile = ?");
//     $check_donor->execute([$mobile]);
//     $donor_exists = $check_donor->rowCount() > 0;
    
//     // Check in volunteers table
//     $check_volunteer = $pdo->prepare("SELECT mobile FROM volunteers WHERE mobile = ?");
//     $check_volunteer->execute([$mobile]);
//     $volunteer_exists = $check_volunteer->rowCount() > 0;
    
//     echo json_encode(['exists' => ($donor_exists || $volunteer_exists)]);
// } else {
//     echo json_encode(['error' => 'No mobile number provided']);
// }
?>
