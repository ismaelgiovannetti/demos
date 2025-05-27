<!DOCTYPE html>
<html>
<head>
    <title>Dèmos : The People</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/ios-button-reset.css">
</head>
<body>
    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="width: 25%; text-align: center; padding: 10px;">
                <form action="index.php" method="get" style="display: inline;">
                    <button type="submit">People</button>
                </form>
            </td>
            <?php if (is_logged_in()): ?>
                <td style="width: 25%; text-align: center; padding: 10px;">
                    <form action="post.php" method="get" style="display: inline;">
                        <button type="submit">Talk</button>
                    </form>
                </td>
                <td style="width: 25%; text-align: center; padding: 10px;">
                    <form action="user.php" method="get" style="display: inline;">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                        <button type="submit"><?php echo htmlspecialchars($_SESSION['username']); ?></button>
                    </form>
                </td>
                <td style="width: 25%; text-align: center; padding: 10px;">
                    <form action="logout.php" method="post" style="display: inline;">
                        <button type="submit">Disconnect</button>
                    </form>
                </td>
            <?php else: ?>
                <td style="width: 25%; text-align: center; padding: 10px;" colspan="2">
                    <form action="login.php" method="get" style="display: inline;">
                        <button type="submit">Connect</button>
                    </form>
                </td>
                <td style="width: 25%; text-align: center; padding: 10px;" colspan="2">
                    <form action="register.php" method="get" style="display: inline;">
                        <button type="submit">Join</button>
                    </form>
                </td>
                <td style="width: 25%; text-align: center; padding: 10px;">
                    <form action="bios.php" method="get" style="display: inline;">
                        <button type="submit">Bios</button>
                    </form>
                </td>
            <?php endif; ?>
        </tr>
    </table>
    <hr>
    <br>
    <?php if (isset($_SESSION['message'])): ?>
        <div style="text-align: center; color: red; margin: 10px 0; font-weight: bold;">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
