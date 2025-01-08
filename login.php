<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
</head>
<body>
    <h1>User Login</h1>
    <form method="POST" action="login_api.php">
        <label for="user_input">Email or Mobile Number:</label>
        <input type="text" id="user_input" name="user_input" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <?php
    if (isset($_GET['message'])) {
        echo '<p>' . htmlspecialchars($_GET['message']) . '</p>';
    }
    ?>
</body>
</html>