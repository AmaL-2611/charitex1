<?php
session_start();
require_once 'config.php';

// Check if orphanage is logged in
if (!isset($_SESSION['orphanage_id'])) {
    header('location:login_orphanage.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['id'])) {
        // First, get the request details to check status and image path
        $stmt = $pdo->prepare("
            SELECT * FROM funding_requests 
            WHERE id = ? AND orphanage_id = ? AND status = 'pending'
        ");
        $stmt->execute([$_GET['id'], $_SESSION['orphanage_id']]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($request) {
            // Delete the request
            $stmt = $pdo->prepare("
                DELETE FROM funding_requests 
                WHERE id = ? AND orphanage_id = ? AND status = 'pending'
            ");
            $stmt->execute([$_GET['id'], $_SESSION['orphanage_id']]);

            // Delete the associated image file
            if (file_exists($request['image_path'])) {
                unlink($request['image_path']);
            }

            $_SESSION['success_message'] = "Funding request deleted successfully.";
        }
    }
} catch (PDOException $e) {
    error_log("Delete Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to delete the funding request.";
}

header('location:my_funding_requests.php');
exit(); 