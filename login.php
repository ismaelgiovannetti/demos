<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['message'] = 'Please fill in all fields';
        redirect('login.php');
    }
    
    try {
        $stmt = $db->prepare('SELECT id, username, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['message'] = 'Login successful!';
            redirect('index.php');
        } else {
            // Log failed login attempt
            error_log("Failed login attempt for username: $username");
            $_SESSION['message'] = 'Invalid username or password';
            redirect('login.php');
        }
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        $_SESSION['message'] = 'An error occurred. Please try again.';
        redirect('login.php');
    }
}
?>
<?php include 'includes/header.php'; ?>

<h1>Login</h1>
<form method="post" action="login.php">
    <table>
        <tr>
            <td>Username:</td>
            <td><input type="text" name="username" required></td>
        </tr>
        <tr>
            <td>Password:</td>
            <td><input type="password" name="password" required></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" value="Login"></td>
        </tr>
    </table>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>

<?php include 'includes/footer.php'; ?>
