<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db_config.php');
    
    $username = $_POST['username'];
	$password = $_POST['password'];
	
	//$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	
    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
</head>
<body>
    <h2>Signup</h2>
    <form action="signup.php" method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        
        <input type="submit" value="Signup">
    </form>
    <a href="login.php">Already have an account? Login</a>
</body>
</html>
