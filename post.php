<?php
try {
    require_once 'includes/config.php';
    
    if (!is_logged_in()) {
        $_SESSION['message'] = 'Who are you ? Connect to the People to Talk.';
        redirect('login.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        
        if (empty($content)) {
            $_SESSION['message'] = 'Post content cannot be empty';
            redirect('post.php');
            exit();
        }
        
        if (strlen($content) > 280) {
            $_SESSION['message'] = 'Post must be 280 characters or less';
            redirect('post.php');
            exit();
        }
        
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare('INSERT INTO posts (user_id, content, upvotes, downvotes) VALUES (?, ?, 0, 0)');
            $stmt->execute([get_current_user_id(), $content]);
            
            $db->commit();
            
            $_SESSION['message'] = 'Post created successfully!';
            redirect('index.php');
            exit();
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Post creation error: ' . $e->getMessage());
            $_SESSION['message'] = 'Failed to create post. Error: ' . $e->getMessage();
            redirect('post.php');
            exit();
        }
    }
} catch (Exception $e) {
    error_log('Unexpected error in post.php: ' . $e->getMessage());
    $_SESSION['message'] = 'An unexpected error occurred. Please try again.';
    if (!headers_sent()) {
        header('Location: index.php');
    }
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<h1>Create a New Post</h1>

<?php if (isset($_SESSION['message'])): ?>
    <div>
        <?php 
        echo htmlspecialchars($_SESSION['message']); 
        unset($_SESSION['message']);
        ?>
    </div>
<?php endif; ?>

<form method="post" action="post.php">
    <div>
        <textarea name="content" rows="5" cols="50" maxlength="280" required><?php 
            echo htmlspecialchars($_POST['content'] ?? ''); 
        ?></textarea>
        <div>Max 280 characters</div>
    </div>
    <div>
        <input type="submit" value="Post">
        <a href="index.php">Cancel</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
