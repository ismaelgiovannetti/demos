<!DOCTYPE html>
<html>
<head>
    <title>Dèmos : The people</title>
    <meta charset="UTF-8">
</head>
<body>
    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="width: 25%; text-align: center; padding: 10px;"><a href="index.php">Home</a></td>
            <?php if (is_logged_in()): ?>
                <td style="width: 25%; text-align: center; padding: 10px;"><a href="post.php">New Post</a></td>
                <td style="width: 25%; text-align: center; padding: 10px;"><a href="user.php?username=<?php echo htmlspecialchars($_SESSION['username']); ?>">My Profile</a></td>
                <td style="width: 25%; text-align: center; padding: 10px;"><a href="logout.php">Logout</a></td>
            <?php else: ?>
                <td style="width: 50%; text-align: center; padding: 10px;" colspan="2"><a href="login.php">Login</a></td>
                <td style="width: 50%; text-align: center; padding: 10px;" colspan="2"><a href="register.php">Register</a></td>
            <?php endif; ?>
        </tr>
    </table>
    <br>
    <?php if (isset($_SESSION['message'])): ?>
        <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
