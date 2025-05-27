<!DOCTYPE html>
<html>
<head>
    <title>Dèmos : The People</title>
    <meta charset="UTF-8">
</head>
<body>
    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="width: 25%; text-align: center; padding: 10px;"><a href="index.php">The People</a></td>
            <?php if (is_logged_in()): ?>
                <td style="width: 25%; text-align: center; padding: 10px;"><a style="text-decoration: none; color: black; background-color:rgb(230, 230, 230);" href="post.php">Talk</a></td>
                <td style="width: 25%; text-align: center; padding: 10px;"><a style="text-decoration: none; color: black; background-color:rgb(230, 230, 230);" href="user.php?username=<?php echo htmlspecialchars($_SESSION['username']); ?>"><?php echo htmlspecialchars($_SESSION['username']); ?></a></td>
                <td style="width: 25%; text-align: center; padding: 10px;"><a style="text-decoration: none; color: black; background-color:rgb(230, 230, 230);" href="logout.php">Disconnect</a></td>
            <?php else: ?>
                <td style="width: 50%; text-align: center; padding: 10px;" colspan="2"><a href="login.php">Connect</a></td>
                <td style="width: 50%; text-align: center; padding: 10px;" colspan="2"><a href="register.php">Join</a></td>
            <?php endif; ?>
        </tr>
    </table>
    <br>
    <?php if (isset($_SESSION['message'])): ?>
        <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
