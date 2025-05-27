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

<div style="max-width: 400px; margin: 0 auto; padding: 20px; text-align: center;">
    <h1>Connect to the People</h1>
    <form method="post" action="login.php" style="margin: 20px 0;">
        <table style="margin: 0 auto; text-align: left;">
            <tr>
                <td style="padding: 5px;">Username:</td>
                <td style="padding: 5px;"><input type="text" name="username" style="width: 100%;"></td>
            </tr>
            <tr>
                <td style="padding: 5px;">Password:</td>
                <td style="padding: 5px;"><input type="password" name="password" style="width: 100%;"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; padding-top: 15px;">
                    <input type="submit" value="Connect">
                </td>
            </tr>
        </table>
    </form>
    <p>New here? <a href="register.php">Join the People</a></p>
</div>

<?php include 'includes/footer.php'; ?>
