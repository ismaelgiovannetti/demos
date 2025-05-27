<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['message'] = 'Dèmos : What is your name ?';
        redirect('register.php');
    }
    
    if (strlen($password) < 8) {
        $_SESSION['message'] = 'Dèmos : Password... Longer...';
        redirect('register.php');
    }
    
    try {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute([$username, $hashedPassword]);
        
        $_SESSION['message'] = 'Dèmos : Welcome to the People. Now connect.';
        redirect('login.php');
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            $_SESSION['message'] = 'Dèmos : I already know you. Connect.';
        } else {
            error_log('Registration error: ' . $e->getMessage());
            $_SESSION['message'] = 'Dèmos : Mhh...';
        }
        redirect('register.php');
    }
}
?>
<?php include 'includes/header.php'; ?>

<h1>Join the People</h1>
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
<p>I know you... <a href="login.php">Connect</a></p>

<?php include 'includes/footer.php'; ?>
