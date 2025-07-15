<?php
require_once '../config/database.php';

// Require admin login
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $donor_id = (int)($_POST['donor_id'] ?? 0);
    $new_status = sanitizeInput($_POST['status'] ?? '');
    
    // Validate inputs
    if (!$donor_id || !in_array($new_status, ['active', 'pending', 'inactive'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }
    
    // Verify donor exists and is actually a donor
    $stmt = $pdo->prepare("SELECT id, full_name, status FROM users WHERE id = ? AND user_type = 'donor'");
    $stmt->execute([$donor_id]);
    $donor = $stmt->fetch();
    
    if (!$donor) {
        echo json_encode(['success' => false, 'message' => 'Donor not found']);
        exit;
    }
    
    // Update donor status
    $stmt = $pdo->prepare("UPDATE users SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $success = $stmt->execute([$new_status, $donor_id]);
    
    if ($success) {
        // Log the status change (optional - you can add a logs table later)
        error_log("Admin {$_SESSION['user_id']} changed donor {$donor_id} status from {$donor['status']} to {$new_status}");
        
        echo json_encode([
            'success' => true, 
            'message' => "Donor status updated to " . ucfirst($new_status),
            'new_status' => $new_status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
    
} catch (Exception $e) {
    error_log("Error updating donor status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>