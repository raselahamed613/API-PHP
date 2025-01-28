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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Data</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Sensor Data Table</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Device ID</th>
                <th>Low Sensor</th>
                <th>Mid-Low Sensor</th>
                <th>Mid Sensor</th>
                <th>Full Sensor</th>
                <th>Motor State</th>
                <th>SSID</th>
                <th>IP</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody id="data-table">
            <!-- Data will be populated here -->
        </tbody>
    </table>

    <script>
        fetch('fetch_water_pump.php')
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('data-table');
                data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.id}</td>
                        <td>${row.device_id}</td>
                        <td>${row.lowSensor}</td>
                        <td>${row.midLowSensor}</td>
                        <td>${row.midSensor}</td>
                        <td>${row.fullSensor}</td>
                        <td>${row.motorState}</td>
                        <td>${row.ssid}</td>
                        <td>${row.ip}</td>
                        <td>${row.timestamp}</td>
                    `;
                    tableBody.appendChild(tr);
                });
            })
            .catch(error => console.error('Error fetching data:', error));
    </script>
    <a href="home.php">Back to Home</a>
</body>
</html>
