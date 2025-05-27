<?php
try {
    require_once 'includes/config.php';
    
    if (!is_logged_in()) {
        $_SESSION['message'] = 'Dèmos : Who are you ? Connect to the People to Talk.';
        redirect('login.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        
        if (empty($content)) {
            $_SESSION['message'] = 'Dèmos : So silent...';
            redirect('post.php');
            exit();
        }
        
        if (strlen($content) > 280) {
            $_SESSION['message'] = 'Dèmos : Talk less...';
            redirect('post.php');
            exit();
        }
        
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare('INSERT INTO posts (user_id, content, upvotes, downvotes) VALUES (?, ?, 0, 0)');
            $stmt->execute([get_current_user_id(), $content]);
            
            $db->commit();
            
            $_SESSION['message'] = 'Dèmos : The People are listening...';
            redirect('index.php');
            exit();
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Post creation error: ' . $e->getMessage());
            $_SESSION['message'] = 'Dèmos : Mhh...';
            redirect('post.php');
            exit();
        }
    }
} catch (Exception $e) {
    error_log('Unexpected error in post.php: ' . $e->getMessage());
    $_SESSION['message'] = 'Dèmos : Mhh...';
    if (!headers_sent()) {
        header('Location: index.php');
    }
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 600px; margin: 0 auto; padding: 20px; text-align: center;">
    <h1>Talk to the People</h1>
    
    <form method="post" action="post.php" style="margin: 20px 0;">
        <div style="margin-bottom: 15px;">
            <textarea name="content" rows="6" style="width: 100%; max-width: 100%; padding: 10px; box-sizing: border-box;" maxlength="280" required><?php 
                echo htmlspecialchars($_POST['content'] ?? ''); 
            ?></textarea>
            <div style="text-align: right; color: #666; font-size: 0.9em; margin-top: 5px;">Maximum 280 characters</div>
        </div>
        <div>
            <input type="submit" value="Talk" style="padding: 8px 20px; cursor: pointer;">
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
