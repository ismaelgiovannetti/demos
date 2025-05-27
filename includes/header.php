<!DOCTYPE html>
<html>
<head>
    <title>Dèmos : The people</title>
    <meta charset="UTF-8">
</head>
<body>
    <table width="100%">
        <tr>
            <td><a href="index.php">Home</a></td>
            <?php if (is_logged_in()): ?>
                <td><a href="post.php">New Post</a></td>
                <td><a href="user.php?username=<?php echo htmlspecialchars($_SESSION['username']); ?>">My Profile</a></td>
                <td><a href="logout.php">Logout</a></td>
            <?php else: ?>
                <td><a href="login.php">Login</a></td>
                <td><a href="register.php">Register</a></td>
            <?php endif; ?>
        </tr>
    </table>
    <br>
    <?php if (isset($_SESSION['message'])): ?>
        <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
