<?php
require_once 'includes/config.php';

$username = $_GET['username'] ?? '';

if (empty($username)) {
    $_SESSION['message'] = 'Dèmos : Who are you looking for ?';
    redirect('index.php');
}

// Get user info with social credit
$query = 'SELECT id, username, social_credit FROM users WHERE username = ?';
$stmt = $db->prepare($query);
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['message'] = 'Dèmos : I don\'t know this one...';
    redirect('index.php');
}

// Get user's posts with vote counts
$query = '
    SELECT p.*, u.username,
           (SELECT vote_type FROM votes WHERE user_id = ' . (is_logged_in() ? get_current_user_id() : '0') . ' AND post_id = p.id) as user_vote
    FROM posts p 
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = :user_id 
    ORDER BY p.created_at DESC';

$stmt = $db->prepare($query);
$stmt->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 800px; margin: 0 auto; padding: 0 20px;">
    <div style="text-align: center; margin: 20px 0;">
        <h1>Dèmos : <?php echo htmlspecialchars($user['username']); ?></h1>
        
        <?php if (is_logged_in() && get_current_user_id() == $user['id']): ?>
            <div style="margin: 10px 0; padding: 10px; background: #f5f5f5;">
                <p><strong>Your Stats:</strong></p>
                <p>Social Credit: <?php echo (int)$user['social_credit']; ?></p>
                <p>Total Posts: <?php echo count($posts); ?></p>
                <?php if (isset($user['status']) && $user['status'] === 'archived'): ?>
                    <p style="color: red; font-weight: bold;">Dèmos : The People have judged you...</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php if (empty($posts)): ?>
    <p style="text-align: center; color: red; margin: 10px 0; font-weight: bold;">Dèmos : So silent... 
    <?php if (is_logged_in() && get_current_user_id() == $user['id']): ?>
        <a href="post.php">Talk.</a>
    <?php endif; ?>
    </p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div>
                    <strong><a href="user.php?username=<?php echo urlencode($post['username']); ?>" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($post['username']); ?></a></strong> - 
                    <?php 
                    $date = new DateTime($post['created_at'], new DateTimeZone('UTC'));
                    $date->setTimezone(new DateTimeZone('Europe/Paris')); // Paris is UTC+2 during DST
                    echo $date->format('M j, Y g:i a'); 
                    ?>
                </div>
                <?php if (is_logged_in()): ?>
                    <?php
                    // Check if current user is archived
                    $stmt = $db->prepare('SELECT status FROM users WHERE id = ?');
                    $stmt->execute([get_current_user_id()]);
                    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!($currentUser && isset($currentUser['status']) && $currentUser['status'] === 'archived')): 
                    ?>
                    <div style="display: flex; gap: 10px;">
                        <button 
                            class="vote-button <?php echo ($post['user_vote'] ?? '') === 'up' ? 'active' : ''; ?>" 
                            data-post-id="<?php echo $post['id']; ?>" 
                            data-vote-type="up"
                            onclick="return handleVote(<?php echo $post['id']; ?>, 'up', this)"
                        >
                            ▲
                        </button>
                        <button 
                            class="vote-button <?php echo ($post['user_vote'] ?? '') === 'down' ? 'active' : ''; ?>" 
                            data-post-id="<?php echo $post['id']; ?>" 
                            data-vote-type="down"
                            onclick="return handleVote(<?php echo $post['id']; ?>, 'down', this)"
                        >
                            ▼
                        </button>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="post-content" style="margin: 10px 0 20px 0; font-size: 1.1em;"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
            <hr>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
function handleVote(postId, voteType, button) {
    // Get all vote buttons for this post
    const upButton = document.querySelector(`button[data-post-id="${postId}"][data-vote-type="up"]`);
    const downButton = document.querySelector(`button[data-post-id="${postId}"][data-vote-type="down"]`);
    
    // Determine the new vote state
    let newVoteType = null;
    let isRemovingVote = false;
    
    if (voteType === 'up') {
        if (upButton.classList.contains('active')) {
            // If upvote is already active, remove the vote by toggling it off
            upButton.classList.remove('active');
            isRemovingVote = true;
        } else {
            // Set upvote and remove downvote
            upButton.classList.add('active');
            downButton.classList.remove('active');
            newVoteType = 'up';
        }
    } else {
        if (downButton.classList.contains('active')) {
            // If downvote is already active, remove the vote by toggling it off
            downButton.classList.remove('active');
            isRemovingVote = true;
        } else {
            // Set downvote and remove upvote
            downButton.classList.add('active');
            upButton.classList.remove('active');
            newVoteType = 'down';
        }
    }
    
    // Disable the buttons during the request
    const buttons = [upButton, downButton];
    buttons.forEach(btn => btn.disabled = true);
    
    // Show loading state
    const originalText = button.textContent;
    button.textContent = '...';
    
    // Prepare the form data
    const formData = new FormData();
    formData.append('post_id', postId);
    
    // If removing a vote, we need to send the opposite of the current vote type
    if (isRemovingVote) {
        formData.append('vote_type', voteType);
    } else {
        formData.append('vote_type', newVoteType);
    }
    
    // Send the AJAX request
    fetch('vote.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            // Revert UI on error
            upButton.classList.toggle('active', data.user_vote === 'up');
            downButton.classList.toggle('active', data.user_vote === 'down');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your vote.');
        // Revert UI on error
        upButton.classList.toggle('active', upButton.classList.contains('active') ? false : true);
        downButton.classList.toggle('active', downButton.classList.contains('active') ? false : true);
    })
    .finally(() => {
        // Re-enable buttons and restore text
        buttons.forEach(btn => {
            btn.disabled = false;
            if (btn.dataset.voteType === 'up') btn.textContent = '▲';
            if (btn.dataset.voteType === 'down') btn.textContent = '▼';
        });

        // Refresh the page to update the social credit display
        location.reload();
    });
    
    // Prevent form submission
    return false;
}
</script>

<style>
.post-content {
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    white-space: pre-wrap;
}

.vote-button {
    background: none;
    border: 1px solid #ccc;
    border-radius: 3px;
    cursor: pointer;
    padding: 2px 8px;
    transition: all 0.2s;
}
.vote-button:hover {
    background: #f0f0f0;
}
.vote-button[data-vote-type="up"].active {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}
.vote-button[data-vote-type="down"].active {
    background: #f44336;
    color: white;
    border-color: #f44336;
}
</style>

<?php include 'includes/footer.php'; ?>
