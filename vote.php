<?php
require_once 'includes/config.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$post_id = (int)($_POST['post_id'] ?? 0);
$vote_type = $_POST['vote_type'] ?? '';
$user_id = get_current_user_id();

if (!$post_id || !in_array($vote_type, ['up', 'down'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

try {
    $db->beginTransaction();
    
    // Check if user already voted on this post
    $stmt = $db->prepare('SELECT id, vote_type FROM votes WHERE user_id = ? AND post_id = ?');
    $stmt->execute([$user_id, $post_id]);
    $existing_vote = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get post author ID for social credit update
    $stmt = $db->prepare('SELECT user_id FROM posts WHERE id = ?');
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        throw new Exception('Post not found');
    }
    
    $post_author_id = $post['user_id'];
    
    if ($existing_vote) {
        // User already voted, check if it's the same vote type
        if ($existing_vote['vote_type'] === $vote_type) {
            // Remove the vote
            $stmt = $db->prepare('DELETE FROM votes WHERE id = ?');
            $stmt->execute([$existing_vote['id']]);
            
            // Update post votes
            $field = $vote_type === 'up' ? 'upvotes' : 'downvotes';
            $stmt = $db->prepare("UPDATE posts SET $field = $field - 1 WHERE id = ?");
            $stmt->execute([$post_id]);
            
            // Update social credit
            $credit_change = $vote_type === 'up' ? -1 : 1;
            $stmt = $db->prepare('UPDATE users SET social_credit = social_credit + ? WHERE id = ?');
            $stmt->execute([$credit_change, $post_author_id]);
        } else {
            // Change vote type
            $stmt = $db->prepare('UPDATE votes SET vote_type = ? WHERE id = ?');
            $stmt->execute([$vote_type, $existing_vote['id']]);
            
            // Update post votes (remove old vote, add new one)
            $old_field = $existing_vote['vote_type'] === 'up' ? 'upvotes' : 'downvotes';
            $new_field = $vote_type === 'up' ? 'upvotes' : 'downvotes';
            
            $stmt = $db->prepare("UPDATE posts SET $old_field = $old_field - 1, $new_field = $new_field + 1 WHERE id = ?");
            $stmt->execute([$post_id]);
            
            // Update social credit (reverse old vote, apply new one)
            $credit_change = $vote_type === 'up' ? 2 : -2; // -1 for removing downvote, +1 for adding upvote or vice versa
            $stmt = $db->prepare('UPDATE users SET social_credit = social_credit + ? WHERE id = ?');
            $stmt->execute([$credit_change, $post_author_id]);
        }
    } else {
        // New vote
        $stmt = $db->prepare('INSERT INTO votes (user_id, post_id, vote_type) VALUES (?, ?, ?)');
        $stmt->execute([$user_id, $post_id, $vote_type]);
        
        // Update post votes
        $field = $vote_type === 'up' ? 'upvotes' : 'downvotes';
        $stmt = $db->prepare("UPDATE posts SET $field = $field + 1 WHERE id = ?");
        $stmt->execute([$post_id]);
        
        // Update social credit
        $credit_change = $vote_type === 'up' ? 1 : -1;
        $stmt = $db->prepare('UPDATE users SET social_credit = social_credit + ? WHERE id = ?');
        $stmt->execute([$credit_change, $post_author_id]);
    }
    
    // Get updated vote counts and social credit
    $stmt = $db->prepare('SELECT upvotes, downvotes FROM posts WHERE id = ?');
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare('SELECT social_credit FROM users WHERE id = ?');
    $stmt->execute([$post_author_id]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'upvotes' => (int)$post['upvotes'],
        'downvotes' => (int)$post['downvotes'],
        'social_credit' => (int)$author['social_credit']
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred']);
    error_log('Vote error: ' . $e->getMessage());
}
?>
