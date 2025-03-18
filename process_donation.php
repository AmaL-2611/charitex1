<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: donor.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validate and sanitize inputs
    $funding_request_id = filter_input(INPUT_POST, 'funding_request_id', FILTER_SANITIZE_NUMBER_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $donor_name = filter_input(INPUT_POST, 'donor_name', FILTER_SANITIZE_STRING);
    $donor_email = filter_input(INPUT_POST, 'donor_email', FILTER_SANITIZE_EMAIL);

    // Validate the funding request exists and is approved
    $stmt = $pdo->prepare("
        SELECT * FROM funding_requests 
        WHERE id = ? AND status = 'approved' AND end_date >= CURRENT_DATE
    ");
    $stmt->execute([$funding_request_id]);
    $request = $stmt->fetch();

    if (!$request) {
        throw new Exception('Invalid or expired funding request');
    }

    // Create or get donor record
    $stmt = $pdo->prepare("
        INSERT INTO donors (name, email, created_at) 
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id), name=VALUES(name)
    ");
    $stmt->execute([$donor_name, $donor_email]);
    $donor_id = $pdo->lastInsertId();

    // Create donation record
    $stmt = $pdo->prepare("
        INSERT INTO donations (
            donor_id, funding_request_id, amount, payment_status, created_at
        ) VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$donor_id, $funding_request_id, $amount]);
    $donation_id = $pdo->lastInsertId();

    // Redirect to payment gateway (implement your payment gateway integration here)
    $_SESSION['donation_id'] = $donation_id;
    header('Location: payment_gateway.php');
    exit();

} catch (Exception $e) {
    error_log("Donation processing error: " . $e->getMessage());
    header('Location: donor.php?error=donation_failed');
    exit();
}
?> 