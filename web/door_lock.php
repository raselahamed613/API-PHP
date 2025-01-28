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
    <title>Door Lock</title>
</head>
<body>
    <h2>Door Lock Control</h2>
    <p>Control the door lock here.</p>
    <a href="home.php">Back to Home</a>
</body>
</html>
