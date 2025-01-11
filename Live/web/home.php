<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
    <p>Choose an option:</p>
    <ul>
        <li><a href="water_pump.php">Water Pump</a></li>
        <li><a href="doorlock.php">Door Lock</a></li>
    </ul>
    <a href="login.php">Logout</a>
</body>
</html>
