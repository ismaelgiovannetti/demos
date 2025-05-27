<?php
try {
    require_once 'includes/config.php';

    // Get selected date from URL or use today's date
    $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Build the query with date filter
    $query = '
        SELECT p.*, u.username, u.social_credit,
               COALESCE(p.upvotes, 0) as upvotes,
               COALESCE(p.downvotes, 0) as downvotes
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE DATE(p.created_at) = :selected_date
        ORDER BY p.created_at DESC
    ';
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':selected_date', $selectedDate);

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

<script>
function handleVote(postId, voteType, button) {
    // Disable the button to prevent multiple clicks
    const buttons = document.querySelectorAll(`button[data-post-id="${postId}"]`);
    buttons.forEach(btn => btn.disabled = true);
    
    // Show loading state
    const originalText = button.textContent;
    button.textContent = '...';
    
    // Prepare the form data
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('vote_type', voteType);
    
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
        } else {
            // Update the vote counts
            const upvotesEl = document.getElementById(`upvotes-${postId}`);
            const downvotesEl = document.getElementById(`downvotes-${postId}`);
            const socialCreditEl = document.querySelector(`[data-post-id="${postId}"] .social-credit`);
            
            if (upvotesEl) upvotesEl.textContent = data.upvotes;
            if (downvotesEl) downvotesEl.textContent = data.downvotes;
            if (socialCreditEl) socialCreditEl.textContent = data.social_credit;
            
            // Toggle active class on buttons
            const upButton = document.querySelector(`button[data-post-id="${postId}"][data-vote-type="up"]`);
            const downButton = document.querySelector(`button[data-post-id="${postId}"][data-vote-type="down"]`);
            
            if (voteType === 'up') {
                upButton.classList.toggle('active', data.user_vote === 'up');
                downButton.classList.remove('active');
            } else {
                downButton.classList.toggle('active', data.user_vote === 'down');
                upButton.classList.remove('active');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your vote.');
    })
    .finally(() => {
        // Re-enable buttons and restore text
        buttons.forEach(btn => btn.disabled = false);
        button.textContent = originalText;
    });
    
    // Prevent form submission
    return false;
}
</script>

<div style="text-align: center; margin-bottom: 20px;">
    <h1>Dèmos : The people</h1>
    <form id="dateForm" style="margin: 20px 0;">
        <input type="date" id="datePicker" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>">
    </form>
</div>

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
    <p style="text-align: center; color: red; margin: 10px 0; font-weight: bold;">Dèmos : So silent... Talk.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <div>
            <p>
                <strong><?php echo htmlspecialchars($post['username']); ?></strong> 
                (Social: <span class="social-credit" data-post-id="<?php echo $post['id']; ?>"><?php echo (int)$post['social_credit']; ?></span>) - 
                <?php echo date('M j, Y g:i a', strtotime($post['created_at'])); ?>
            </p>
            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            <p>
                Votes: 
                <span id="upvotes-<?php echo $post['id']; ?>"><?php echo (int)$post['upvotes']; ?></span> up / 
                <span id="downvotes-<?php echo $post['id']; ?>"><?php echo (int)$post['downvotes']; ?></span> down
                <?php if (is_logged_in()): ?>
                    | 
                    <button 
                        class="vote-button <?php echo ($post['user_vote'] ?? '') === 'up' ? 'active' : ''; ?>" 
                        data-post-id="<?php echo $post['id']; ?>" 
                        data-vote-type="up"
                        onclick="return handleVote(<?php echo $post['id']; ?>, 'up', this)">
                        Upvote
                    </button> | 
                    <button 
                        class="vote-button <?php echo ($post['user_vote'] ?? '') === 'down' ? 'active' : ''; ?>" 
                        data-post-id="<?php echo $post['id']; ?>" 
                        data-vote-type="down"
                        onclick="return handleVote(<?php echo $post['id']; ?>, 'down', this)">
                        Downvote
                    </button>
                <?php endif; ?>
            </p>
            <hr>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
