<?php
try {
    require_once 'includes/config.php';

    // Get selected date from URL or use today's date
    $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Build the query with date filter
    $query = '
        SELECT p.*, u.username, 
               (SELECT vote_type FROM votes WHERE post_id = p.id AND user_id = :current_user_id) as user_vote
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE DATE(p.created_at) = :selected_date
        ORDER BY p.created_at DESC
    ';
    
    $stmt = $db->prepare($query);
    $currentUserId = is_logged_in() ? $_SESSION['user_id'] : 0;
    $stmt->bindParam(':selected_date', $selectedDate);
    $stmt->bindParam(':current_user_id', $currentUserId, PDO::PARAM_INT);

    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error in index.php: ' . $e->getMessage());
    $posts = [];
    $_SESSION['message'] = 'Error loading posts. Please try again later.';
} catch (Exception $e) {
    error_log('Unexpected error in index.php: ' . $e->getMessage());
    $posts = [];
    $_SESSION['message'] = 'An unexpected error occurred.';
}
?>
<?php include 'includes/header.php'; ?>



<style>
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
    // because the server will toggle it off if it matches the existing vote
    if (isRemovingVote) {
        formData.append('vote_type', voteType); // Send the same type to toggle it off
    } else {
        formData.append('vote_type', newVoteType); // Send the new vote type
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
    });
    
    // Prevent form submission
    return false;
}
</script>

<div style="text-align: center; margin-bottom: 20px;">
    <h1>Dèmos : The People</h1>
    <form id="dateForm" style="margin: 20px 0;">
        <input type="date" id="datePicker" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>">
    </form>
</div>

<div style="max-width: 800px; margin: 0 auto; padding: 0 20px;">

<script>
// Auto-submit form when date changes
document.getElementById('datePicker').addEventListener('change', function() {
    document.getElementById('dateForm').submit();
});

// Set default date to today if no date is selected
if (!new URLSearchParams(window.location.search).has('date')) {
    const today = new Date().toISOString().split('T')[0];
    window.history.replaceState({}, '', `${window.location.pathname}?date=${today}`);
}
</script>

<?php if (empty($posts)): ?>
    <p style="text-align: center; color: red; margin: 10px 0; font-weight: bold;">Dèmos : So silent... <a href="post.php">Talk.</a></p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div>
                    <strong><a href="user.php?username=<?php echo urlencode($post['username']); ?>" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($post['username']); ?></a></strong> - 
                    <?php echo date('g:i a', strtotime($post['created_at'])); ?>
                </div>
                <?php if (is_logged_in()): ?>
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
            </div>
            <p style="margin: 10px 0 20px 0; font-size: 1.1em;"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            <hr>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
