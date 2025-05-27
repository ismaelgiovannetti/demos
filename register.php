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

<div style="max-width: 400px; margin: 0 auto; padding: 20px; text-align: center;">
    <h1>Join the People</h1>
    <form method="post" action="register.php" style="margin: 20px 0;">
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
                    <input type="submit" value="Join">
                </td>
            </tr>
        </table>
    </form>
    <p>I know you... <a href="login.php">Connect</a></p>
</div>

<?php include 'includes/footer.php'; ?>
