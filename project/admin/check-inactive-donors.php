<?php
require_once '../config/database.php';

// This script should be run via cron job or called periodically
// It automatically sets donors to inactive after 3 months of no activity

try {
    $pdo = getDBConnection();
    
    // Calculate date 3 months ago
    $three_months_ago = date('Y-m-d H:i:s', strtotime('-3 months'));
    
    // Find active donors who haven't been updated in 3 months
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, updated_at 
        FROM users 
        WHERE user_type = 'donor' 
        AND status = 'active' 
        AND updated_at < ?
    ");
    $stmt->execute([$three_months_ago]);
    $inactive_donors = $stmt->fetchAll();
    
    $updated_count = 0;
    
    foreach ($inactive_donors as $donor) {
        // Update donor to inactive
        $update_stmt = $pdo->prepare("UPDATE users SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        if ($update_stmt->execute([$donor['id']])) {
            $updated_count++;
            error_log("Auto-deactivated donor {$donor['id']} ({$donor['full_name']}) - last activity: {$donor['updated_at']}");
        }
    }
    
    if (php_sapi_name() === 'cli') {
        echo "Updated {$updated_count} donors to inactive status.\n";
    } else {
        echo json_encode([
            'success' => true,
            'message' => "Updated {$updated_count} donors to inactive status",
            'updated_count' => $updated_count
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error in check-inactive-donors.php: " . $e->getMessage());
    if (php_sapi_name() === 'cli') {
        echo "Error: " . $e->getMessage() . "\n";
    } else {
        echo json_encode(['success' => false, 'message' => 'Error checking inactive donors']);
    }
}
?>