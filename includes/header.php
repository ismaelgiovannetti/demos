<!DOCTYPE html>
<html>
<head>
    <title>Dèmos : The People</title>
    <meta charset="UTF-8">
</head>
<body>
    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="width: 25%; text-align: center; padding: 10px;">
                <form action="index.php" method="get" style="display: inline;">
                    <button type="submit" style="padding: 8px 16px; cursor: pointer;">The People</button>
                </form>
            </td>
            <?php if (is_logged_in()): ?>
                <td style="width: 25%; text-align: center; padding: 10px;">
                    <form action="post.php" method="get" style="display: inline;">
                        <button type="submit" style="padding: 8px 16px; cursor: pointer;">Talk</button>
                    </form>
                </td>
                <td style="width: 25%; text-align: center; padding: 10px;">
                    <form action="user.php" method="get" style="display: inline;">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                        <button type="submit" style="padding: 8px 16px; cursor: pointer;"><?php echo htmlspecialchars($_SESSION['username']); ?></button>
                    </form>
                </td>
                <td style="width: 25%; text-align: center; padding: 10px;">
                    <form action="logout.php" method="post" style="display: inline;">
                        <button type="submit" style="padding: 8px 16px; cursor: pointer;">Disconnect</button>
                    </form>
                </td>
            <?php else: ?>
                <td style="width: 50%; text-align: center; padding: 10px;" colspan="2">
                    <form action="login.php" method="get" style="display: inline;">
                        <button type="submit" style="padding: 8px 16px; cursor: pointer;">Connect</button>
                    </form>
                </td>
                <td style="width: 50%; text-align: center; padding: 10px;" colspan="2">
                    <form action="register.php" method="get" style="display: inline;">
                        <button type="submit" style="padding: 8px 16px; cursor: pointer;">Join</button>
                    </form>
                </td>
            <?php endif; ?>
        </tr>
    </table>
    <br>
    <?php if (isset($_SESSION['message'])): ?>
        <p><?php echo htmlspecialchars($_SESSION['message']); ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
