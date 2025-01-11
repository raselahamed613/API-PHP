<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
</head>
<body>
    <h1>User Registration</h1>
    <form action="register_api.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="mobile">Mobile:</label>
        <input type="tel" id="mobile" name="mobile" required><br><br>

        <label for="device_id">Device ID:</label>
        <input type="text" id="device_id" name="device_id" required><br><br>
        
        <label for="additional">Additional Info:</label>
        <textarea id="additional" name="additional"></textarea><br><br>
        
        <button type="submit">Register</button>
        
    </form>

</body>
</html>
