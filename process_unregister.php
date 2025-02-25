<?php
session_start();
require_once 'connect.php';

// Check if user is logged in and is a volunteer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'volunteer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);
    $volunteer_id = $_SESSION['user_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete the registration
        $delete_stmt = $pdo->prepare("
            DELETE FROM event_registrations 
            WHERE event_id = ? AND volunteer_id = ?
        ");
        $delete_stmt->execute([$event_id, $volunteer_id]);

        // Update the current_volunteers count in events table
        $update_stmt = $pdo->prepare("
            UPDATE events 
            SET current_volunteers = current_volunteers - 1 
            WHERE id = ? AND current_volunteers > 0
        ");
        $update_stmt->execute([$event_id]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success_message'] = "Successfully unregistered from the event.";
        header("Location: volunteer_dashboard.php");
        exit();

    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Unregister Error: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred while unregistering. Please try again.";
        header("Location: volunteer_dashboard.php");
        exit();
    }
} else {
    header("Location: volunteer_dashboard.php");
    exit();
}
?>
