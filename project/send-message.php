<?php
require_once 'config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to send messages']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_id = (int)($_POST['donor_id'] ?? 0);
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    $sender_id = $_SESSION['user_id'];
    
    // Validation
    if (empty($subject) || empty($message) || empty($donor_id)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Verify donor exists and is active
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE id = ? AND user_type = 'donor' AND status = 'active'");
        $stmt->execute([$donor_id]);
        $donor = $stmt->fetch();
        
        if (!$donor) {
            echo json_encode(['success' => false, 'message' => 'Invalid donor selected']);
            exit;
        }
        
        // Insert message
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$sender_id, $donor_id, $subject, $message])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Message sent successfully to ' . $donor['full_name']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        }
        
    } catch (Exception $e) {
        error_log("Send message error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while sending the message']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>