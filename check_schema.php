<?php
require_once 'includes/config.php';

try {
    // Check if status column exists and show its definition
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'status'");
    $statusColumn = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$statusColumn) {
        echo "Status column does not exist in users table.\n";
        echo "You need to run the schema update first.\n";
    } else {
        echo "Status column exists. Current definition:\n";
        print_r($statusColumn);
    }
    
    // Show current user count and test user status
    $userCount = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    echo "\nTotal users: " . $userCount . "\n";
    
    // Show test user status
    $testUser = $db->query("SELECT id, username, social_credit, status FROM users WHERE social_credit < 0 ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($testUser) {
        echo "\nTest user with negative social credit:\n";
        print_r($testUser);
        
        // Check if user would be archived by our conditions
        $shouldBeArchived = ($testUser['social_credit'] < 0 && abs($testUser['social_credit']) > $userCount);
        echo "\nShould be archived based on conditions: " . ($shouldBeArchived ? 'YES' : 'NO') . "\n";
        echo "(social_credit = {$testUser['social_credit']}, user count = $userCount)\n";
    } else {
        echo "\nNo users with negative social credit found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
