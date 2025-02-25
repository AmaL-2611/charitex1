<?php
session_start();
require_once 'connect.php';
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'] ?? null;
    $hours = $_POST['hours'] ?? null;

    if (!$event_id || !$hours) {
        $_SESSION['error'] = "Please provide both event and hours.";
        header("Location: volunteer.php");
        exit();
    }

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verify event belongs to this volunteer
        $stmt = $pdo->prepare("
            SELECT * FROM event_registrations 
            WHERE event_id = ? AND volunteer_id = ?
        ");
        $stmt->execute([$event_id, $_SESSION['user_id']]);
        $registration = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$registration) {
            $_SESSION['error'] = "You are not registered for this event.";
            header("Location: volunteer.php");
            exit();
        }

        // Log hours
        $log_stmt = $pdo->prepare("
            INSERT INTO volunteer_hours 
            (volunteer_id, event_id, hours_worked, logged_date) 
            VALUES (?, ?, ?, NOW())
        ");
        $log_stmt->execute([
            $_SESSION['user_id'], 
            $event_id, 
            $hours
        ]);

        // Update total hours in volunteers table
        $update_stmt = $pdo->prepare("
            UPDATE volunteers 
            SET total_hours = total_hours + ? 
            WHERE id = ?
        ");
        $update_stmt->execute([$hours, $_SESSION['user_id']]);

        $_SESSION['success'] = "Hours logged successfully!";
        header("Location: volunteer.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: volunteer.php");
        exit();
    }
} else {
    header("Location: volunteer.php");
    exit();
}
?>
