<?php
require_once 'includes/config.php';

// Get all users with their social credit and post count
$query = '
    SELECT u.id, u.username, u.social_credit, 
           COUNT(p.id) as post_count
    FROM users u
    LEFT JOIN posts p ON u.id = p.user_id
    GROUP BY u.id
    ORDER BY u.username';

$stmt = $db->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 800px; margin: 0 auto; padding: 0 20px;">
    <div style="text-align: center; margin: 20px 0;">
        <h1>Dèmos : The People</h1>
        <p>Meet the community members</p>
    </div>

    <div style="margin-top: 30px;">
        <?php foreach ($users as $user): ?>
            <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0;">
                        <a href="user.php?username=<?php echo urlencode($user['username']); ?>" 
                           style="color: inherit; text-decoration: none;">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                    </h2>
                    <div>
                        <span style="margin-right: 15px;">Posts: <?php echo (int)$user['post_count']; ?></span>
                        <span>Social Credit: <?php echo (int)$user['social_credit']; ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
