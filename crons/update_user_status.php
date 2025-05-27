<?php
/**
 * This script updates user statuses based on their social credit score.
 * It will be executed daily via cron job.
 * 
 * Conditions for archiving a user:
 * 1. Their social_credit is negative
 * 2. The absolute value of their social_credit is greater than the total number of users
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/../includes/config.php';

// Initialize log array to track script execution
$log = [];
$log[] = '[' . date('Y-m-d H:i:s') . '] Starting user status update script';

try {
    // Begin transaction
    $db->beginTransaction();

    // Get the total number of active users (status is NULL or 'active')
    $stmt = $db->query("SELECT COUNT(*) as total_users FROM users WHERE status IS NULL OR status = 'active'");
    $totalUsers = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    $log[] = "Total active users in system: $totalUsers";

    // Find users to archive
    $query = "
        SELECT id, username, social_credit 
        FROM users 
        WHERE social_credit < 0 
        AND social_credit < -:total_users
        AND (status IS NULL OR status != 'archived')
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':total_users', $totalUsers, PDO::PARAM_INT);
    $stmt->execute();
    $usersToArchive = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $usersCount = count($usersToArchive);
    
    $log[] = "Found $usersCount users to archive";
    
    // Archive users if any found
    if ($usersCount > 0) {
        $userIds = array_column($usersToArchive, 'id');
        $placeholders = rtrim(str_repeat('?,', count($userIds)), ',');
        
        $updateQuery = "UPDATE users SET status = 'archived' WHERE id IN ($placeholders)";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute($userIds);
        
        // Log archived users
        foreach ($usersToArchive as $user) {
            $log[] = sprintf(
                "Archived user: ID=%d, Username=%s, Social Credit=%d",
                $user['id'],
                $user['username'],
                $user['social_credit']
            );
        }
    }
    
    // Commit transaction
    $db->commit();
    $log[] = 'User status update completed successfully';
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $log[] = 'Error: ' . $e->getMessage();
    http_response_code(500);
}

// Log the execution
$log[] = '[' . date('Y-m-d H:i:s') . '] Script execution completed';
$logContent = implode("\n", $log) . "\n\n";

// Write to log file
$logFile = __DIR__ . '/update_user_status.log';
file_put_contents($logFile, $logContent, FILE_APPEND);

// Output log for cron job
if (php_sapi_name() === 'cli') {
    echo implode("\n", $log) . "\n";
}
?>
