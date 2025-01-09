<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
</head>
<body>
    <h1>User Registration</h1>
    <form id="registrationForm">
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

        <button type="button" onclick="submitForm()">Register</button>
    </form>

    <p id="responseMessage"></p>

    <script>
        async function submitForm() {
            const formData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value,
                email: document.getElementById('email').value,
                mobile: document.getElementById('mobile').value,
                additional: document.getElementById('additional').value,
                device_id: document.getElementById('device_id').value
            };

            const response = await fetch('http://localhost/register_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            const result = await response.json();
            document.getElementById('responseMessage').innerText = result.message || result.error;
        }
    </script>
</body>
</html>
