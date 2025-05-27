<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username)) {
        $_SESSION['message'] = 'Dèmos : What is your name ?';
        redirect('login.php');
    }
    
    try {
        $stmt = $db->prepare('SELECT id, username, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['message'] = 'Dèmos : Welcome back, ' . htmlspecialchars($user['username']) . '!';
            redirect('index.php');
        } else {
            // Log failed login attempt
            error_log("Failed login attempt for username: $username");
            $_SESSION['message'] = 'Dèmos : I don\'t know you...';
            redirect('login.php');
        }
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        $_SESSION['message'] = 'Dèmos : Mhh...';
        redirect('login.php');
    }
}
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div style="padding: 20px; margin-bottom: 20px;">
        <div style="line-height: 1.6; text-align: justify;">
            <h1 style="text-align: center;">Bios</h1>
            <p>Dèmos: The People is a social experiment born from the shadows of the dead internet theory. Its existence spreads only through whispers. The link is never shared openly. Those who join do so anonymously, leaving behind names, faces, and traces. To enter with a distinctive username is to break the unspoken law.</p>
            
            <p>There are no administrators here. No overseers. The People govern themselves. Each day at midnight, through a ruthless democracy of votes, the unwanted are cast out. Their voices remain, but their presence is erased. For in Dèmos, no talk is ever deletable. Every word lingers. Every trace of thought becomes permanent.</p>
            
            <p>Nothing rules the People. No algorithm. No law.</p>
            <p>The talk is the only way to exist.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>