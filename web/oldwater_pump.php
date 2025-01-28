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
	<title>Tank Sensor Visualization</title>
	
	<style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .tank {
            width: 200px;
            height: 400px;
            border: 2px solid #000;
            position: relative;
            margin: 0 auto;
            background: #e0e0e0;
        }
        .water {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: #00f;
            transition: height 0.5s ease-in-out;
        }
        .sensor-label {
            position: absolute;
            width: 100%;
            text-align: center;
            font-weight: bold;
            color: #fff;
        }
        .sensor-level {
            margin-top: 10px;
        }
    </style>
	
    
</head>
<body>

	<h1>Tank Water Level Visualization</h1>
	
	<div class="tank">
        <!-- Water level -->
        <div class="water" id="water-level" style="height: 0;"></div>
        <!-- Sensor labels -->
        <div class="sensor-label" style="bottom: 0;">Low</div>
        <div class="sensor-label" style="bottom: 25%;">Mid-Low</div>
        <div class="sensor-label" style="bottom: 50%;">Mid</div>
        <div class="sensor-label" style="bottom: 75%;">Full</div>
	</div>

	<div class="sensor-level">
        <strong>Motor State:</strong> <span id="motor-state">OFF</span>
    </div>
	<a href="home.php">Back to Home</a>



// Initial fetch
</script>
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
</body>
</html>
