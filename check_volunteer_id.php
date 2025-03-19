<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $volunteer_code = $data['volunteer_code'];

    try {
        $check_used_stmt = $pdo->prepare("SELECT volunteer_id FROM volunteers WHERE volunteer_id = ?");
        $check_used_stmt->execute([$volunteer_code]);
        
        $exists = $check_used_stmt->fetch() ? true : false;
        echo json_encode(['exists' => $exists]);
    } catch (PDOException $e) {
        error_log("Error checking Volunteer ID: " . $e->getMessage());
        echo json_encode(['exists' => false]);
    }
}
?>