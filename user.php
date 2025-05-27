<?php
require_once 'includes/config.php';

$username = $_GET['username'] ?? '';

if (empty($username)) {
    $_SESSION['message'] = 'No username specified';
    redirect('index.php');
}

// Get user info with social credit
$query = 'SELECT id, username, social_credit FROM users WHERE username = ?';
$stmt = $db->prepare($query);
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['message'] = 'User not found';
    redirect('index.php');
}

// Get user's posts with vote counts
$query = '
    SELECT p.*, 
           COALESCE((SELECT COUNT(*) FROM votes WHERE post_id = p.id AND vote_type = "up"), 0) as upvotes,
           COALESCE((SELECT COUNT(*) FROM votes WHERE post_id = p.id AND vote_type = "down"), 0) as downvotes'
    . (is_logged_in() ? ',
           (SELECT vote_type FROM votes WHERE user_id = :current_user_id AND post_id = p.id) as user_vote' : '') . '
    FROM posts p 
    WHERE p.user_id = :user_id 
    ORDER BY p.created_at DESC';

$stmt = $db->prepare($query);
$stmt->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
if (is_logged_in()) {
    $stmt->bindValue(':current_user_id', get_current_user_id(), PDO::PARAM_INT);
}
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<h1>User: <?php echo htmlspecialchars($user['username']); ?></h1>

<p>Social Credit: <?php echo (int)$user['social_credit']; ?></p>
<p>Posts: <?php echo count($posts); ?></p>

<h2>Posts</h2>

<?php if (empty($posts)): ?>
    <p>No posts yet.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <div>
            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            <p>Posted on: <?php echo date('M j, Y g:i a', strtotime($post['created_at'])); ?></p>
            <p>
                Votes: <?php echo (int)$post['upvotes']; ?> up / <?php echo (int)$post['downvotes']; ?> down
                <?php if (is_logged_in()): ?>
                    | 
                    <a href="vote.php?post_id=<?php echo $post['id']; ?>&vote_type=up">Upvote</a> | 
                    <a href="vote.php?post_id=<?php echo $post['id']; ?>&vote_type=down">Downvote</a>
                <?php endif; ?>
            </p>
            <hr>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
