<?php
/**
 * Donor Activity Tracker
 * This file contains functions to track and update donor activity
 */

require_once '../config/database.php';

/**
 * Update donor's last activity timestamp
 */
function updateDonorActivity($donor_id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_type = 'donor'");
        return $stmt->execute([$donor_id]);
    } catch (Exception $e) {
        error_log("Error updating donor activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get donors who should be marked as inactive
 */
function getDonorsForInactiveStatus($months = 3) {
    try {
        $pdo = getDBConnection();
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$months} months"));
        
        $stmt = $pdo->prepare("
            SELECT id, full_name, email, updated_at, 
                   DATEDIFF(NOW(), updated_at) as days_inactive
            FROM users 
            WHERE user_type = 'donor' 
            AND status = 'active' 
            AND updated_at < ?
            ORDER BY updated_at ASC
        ");
        $stmt->execute([$cutoff_date]);
        
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting inactive donors: " . $e->getMessage());
        return [];
    }
}

/**
 * Get donor activity statistics
 */
function getDonorActivityStats() {
    try {
        $pdo = getDBConnection();
        
        $stats = [];
        
        // Active donors (updated within last 3 months)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE user_type = 'donor' 
            AND status = 'active' 
            AND updated_at >= ?
        ");
        $stmt->execute([date('Y-m-d H:i:s', strtotime('-3 months'))]);
        $stats['recently_active'] = $stmt->fetchColumn();
        
        // Donors inactive for 1-3 months
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE user_type = 'donor' 
            AND status = 'active' 
            AND updated_at < ? 
            AND updated_at >= ?
        ");
        $stmt->execute([
            date('Y-m-d H:i:s', strtotime('-1 month')),
            date('Y-m-d H:i:s', strtotime('-3 months'))
        ]);
        $stats['moderately_inactive'] = $stmt->fetchColumn();
        
        // Donors inactive for more than 3 months (should be auto-inactive)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE user_type = 'donor' 
            AND status = 'active' 
            AND updated_at < ?
        ");
        $stmt->execute([date('Y-m-d H:i:s', strtotime('-3 months'))]);
        $stats['should_be_inactive'] = $stmt->fetchColumn();
        
        return $stats;
    } catch (Exception $e) {
        error_log("Error getting donor activity stats: " . $e->getMessage());
        return [
            'recently_active' => 0,
            'moderately_inactive' => 0,
            'should_be_inactive' => 0
        ];
    }
}

/**
 * Send notification to donors who are about to become inactive
 */
function notifyDonorsBeforeInactive($days_before = 7) {
    try {
        $pdo = getDBConnection();
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-3 months +{$days_before} days"));
        
        $stmt = $pdo->prepare("
            SELECT id, full_name, email, updated_at
            FROM users 
            WHERE user_type = 'donor' 
            AND status = 'active' 
            AND updated_at < ?
            AND updated_at >= ?
        ");
        $stmt->execute([
            $cutoff_date,
            date('Y-m-d H:i:s', strtotime("-3 months +{$days_before} days -1 day"))
        ]);
        
        $donors_to_notify = $stmt->fetchAll();
        
        // Here you would implement email notification logic
        // For now, we'll just log the donors who should be notified
        foreach ($donors_to_notify as $donor) {
            error_log("Should notify donor {$donor['id']} ({$donor['email']}) - will become inactive in {$days_before} days");
        }
        
        return count($donors_to_notify);
    } catch (Exception $e) {
        error_log("Error notifying donors: " . $e->getMessage());
        return 0;
    }
}
?>