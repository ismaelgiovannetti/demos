<?php
require_once 'includes/config.php';

// Check if user is logged in and is admin
$isAdmin = is_logged_in() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Get total number of users
$query = 'SELECT COUNT(*) as total_users FROM users';
$stmt = $db->query($query);
$totalUsers = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Get number of active users (status is NULL or 'active')
$query = "SELECT COUNT(*) as active_users FROM users WHERE status IS NULL OR status = 'active'";
$stmt = $db->query($query);
$activeUsers = (int)$stmt->fetch(PDO::FETCH_ASSOC)['active_users'];

// Get all users with their statuses
$query = 'SELECT id, username, status, social_credit FROM users ORDER BY username';
$users = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 800px; margin: 0 auto; padding: 0 20px;">
    <h1>Dèmos: The People</h1>
    
    <div style="margin: 20px 0; padding: 20px; background: #f5f5f5; border-radius: 5px;">
        <h2>Statistics</h2>
        <p>Total Users: <?php echo $totalUsers; ?></p>
        <p>Active Users: <?php echo $activeUsers; ?></p>
        <p>Inactive Users: <?php echo $totalUsers - $activeUsers; ?></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
