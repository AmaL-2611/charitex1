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
        // First check if the event exists and belongs to this orphanage
        $stmt = $pdo->prepare("
            SELECT id FROM events 
            WHERE id = ? AND created_by = ? AND created_by_type = 'orphanage'
        ");
        $stmt->execute([$_GET['id'], $_SESSION['orphanage_id']]);
        $event = $stmt->fetch();

        if ($event) {
            // Delete any related event registrations first
            $stmt = $pdo->prepare("DELETE FROM event_registrations WHERE event_id = ?");
            $stmt->execute([$_GET['id']]);

            // Then delete the event
            $stmt = $pdo->prepare("
                DELETE FROM events 
                WHERE id = ? AND created_by = ? AND created_by_type = 'orphanage'
            ");
            $stmt->execute([$_GET['id'], $_SESSION['orphanage_id']]);

            $_SESSION['success_message'] = "Event deleted successfully.";
        }
    }
} catch (PDOException $e) {
    error_log("Delete Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to delete the event.";
}

header('location:my_events.php');
exit(); 