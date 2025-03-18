<?php
session_start();
require_once 'connect.php';

// Ensure only volunteers can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'volunteer') {
    header("Location: login.php");
    exit();
}

$volunteer_id = $_SESSION['user_id'];
$certificates = [];
$errors = [];

try {
    // Fetch completed events with volunteer participation
    $stmt = $pdo->prepare("
        SELECT 
            e.title, 
            e.cause,
            e.event_date,
            er.participation_hours,
            er.certificate_issued
        FROM 
            event_registrations er
        JOIN 
            events e ON er.event_id = e.id
        WHERE 
            er.volunteer_id = ? 
            AND e.status = 'completed'
            AND er.participation_status = 'completed'
    ");
    $stmt->execute([$volunteer_id]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Error fetching certificates: " . $e->getMessage();
}

// Handle certificate request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_certificate'])) {
    $event_title = $_POST['event_title'];

    try {
        // Update certificate status
        $update_stmt = $pdo->prepare("
            UPDATE event_registrations er
            JOIN events e ON er.event_id = e.id
            SET er.certificate_issued = 1
            WHERE 
                er.volunteer_id = ? 
                AND e.title = ?
                AND e.status = 'completed'
        ");
        $update_stmt->execute([$volunteer_id, $event_title]);

        // Redirect to prevent form resubmission
        header("Location: participation_certificates.php");
        exit();
    } catch (PDOException $e) {
        $errors[] = "Error requesting certificate: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Participation Certificates - CHARITEX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .certificate-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .certificate-details {
            flex-grow: 1;
        }
        .certificate-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .certificate-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Participation Certificates</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($certificates)): ?>
        <p>No completed events with certificates available.</p>
    <?php else: ?>
        <?php foreach ($certificates as $cert): ?>
            <div class="certificate-card">
                <div class="certificate-details">
                    <h2><?php echo htmlspecialchars($cert['title']); ?></h2>
                    <p><strong>Cause:</strong> <?php echo htmlspecialchars($cert['cause']); ?></p>
                    <p><strong>Event Date:</strong> <?php echo date('F j, Y', strtotime($cert['event_date'])); ?></p>
                    <p><strong>Participation Hours:</strong> <?php echo $cert['participation_hours']; ?> hrs</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($cert['title']); ?>">
                    <button 
                        type="submit" 
                        name="request_certificate" 
                        class="certificate-btn"
                        <?php echo $cert['certificate_issued'] ? 'disabled' : ''; ?>
                    >
                        <?php echo $cert['certificate_issued'] ? 'Certificate Issued' : 'Request Certificate'; ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
