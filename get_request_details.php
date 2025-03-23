<?php
require_once 'config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing request ID']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT fr.*, o.name as orphanage_name, o.email as orphanage_email
        FROM funding_requests fr
        JOIN orphanage o ON fr.orphanage_id = o.id
        WHERE fr.id = ?
    ");
    
    $stmt->execute([$_GET['id']]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($request) {
        echo json_encode($request);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Request not found']);
    }
} catch (PDOException $e) {
    error_log("Error fetching request details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>

<script>
// Add these functions for handling funding requests
function viewRequestDetails(requestId) {
    fetch(`get_request_details.php?id=${requestId}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('requestDetailsContent');
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <img src="${data.image_path}" class="img-fluid mb-3" alt="Request Image">
                    </div>
                    <div class="col-md-6">
                        <h4>${data.title}</h4>
                        <p><strong>Orphanage:</strong> ${data.orphanage_name}</p>
                        <p><strong>Goal Amount:</strong> ₹${data.goal_amount}</p>
                        <p><strong>Current Amount:</strong> ₹${data.current_amount}</p>
                        <p><strong>Duration:</strong> ${data.start_date} - ${data.end_date}</p>
                        <p><strong>Description:</strong></p>
                        <p>${data.description}</p>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
        })
        .catch(error => console.error('Error:', error));
}

function approveRequest(requestId) {
    if (confirm('Are you sure you want to approve this funding request?')) {
        updateRequestStatus(requestId, 'approve');
    }
}

function rejectRequest(requestId) {
    if (confirm('Are you sure you want to reject this funding request?')) {
        updateRequestStatus(requestId, 'reject');
    }
}

function updateRequestStatus(requestId, action) {
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('action', action);

    fetch('update_request_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating request status');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script> 