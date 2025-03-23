<?php
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $verification_code = $_POST['verification_code'];
    $email = $_POST['email'];
    
    $sql = "SELECT * FROM volunteer_verifications 
            WHERE email = ? 
            AND verification_code = ? 
            AND status = 'approved'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Code is valid - allow registration to proceed
        $verification = $result->fetch_assoc();
        
        // Delete the verification request as it's no longer needed
        $delete_sql = "DELETE FROM volunteer_verifications WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $verification['id']);
        $delete_stmt->execute();
        
        echo "valid";
    } else {
        echo "invalid";
    }
}
?> 