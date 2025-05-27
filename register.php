<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['message'] = 'Please fill in all fields';
        redirect('register.php');
    }
    
    if (strlen($password) < 8) {
        $_SESSION['message'] = 'Password must be at least 8 characters long';
        redirect('register.php');
    }
    
    try {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute([$username, $hashedPassword]);
        
        $_SESSION['message'] = 'Registration successful! Please login.';
        redirect('login.php');
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            $_SESSION['message'] = 'Username already exists';
        } else {
            error_log('Registration error: ' . $e->getMessage());
            $_SESSION['message'] = 'Registration failed. Please try again.';
        }
        redirect('register.php');
    }
}
?>
<?php include 'includes/header.php'; ?>

<h1>Register</h1>
<form method="post" action="register.php">
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
            <td><input type="submit" value="Register"></td>
        </tr>
    </table>
</form>
<p>Already have an account? <a href="login.php">Login here</a></p>

<?php include 'includes/footer.php'; ?>
