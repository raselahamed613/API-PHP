<?php
require 'db_connection.php';

echo json_encode(['message' => 'Database connection successful']);
// $sql = "SELECT 
// SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, 
//     LEAD(timestamp) OVER (ORDER BY timestamp), 
//     timestamp)
// )) AS total_time
// FROM 
// motor_logs
// WHERE 
// motorState = 1";
// SQL Query
$sql = "SELECT * 
FROM sensor_data
WHERE motorState = 1
  AND timestamp > (
      SELECT 
          COALESCE(MAX(timestamp), '1970-01-01 00:00:00')
      FROM 
          sensor_data
      WHERE 
          motorState = 0
  )
ORDER BY timestamp ASC;";

// SQL query to get the latest continuous motorState = 1 period and calculate total time
// $sql = "
// SELECT 
//     TIMEDIFF(MAX(timestamp), MIN(timestamp)) AS total_time
// FROM 
//     sensor_data
// WHERE 
//     motorState = 1
//   AND timestamp > (
//       SELECT 
//           COALESCE(MAX(timestamp), '1970-01-01 00:00:00')
//       FROM 
//           sensor_data
//       WHERE 
//           motorState = 0
//   );
// ";
$result = $conn->query($sql);

// Check if query executed successfully
if ($result === false) {
    echo "Error: " . $conn->error;
} else {
    // Fetch result
    if ($row = $result->fetch_assoc()) {
        echo "Total time for the latest period where motorState = 1: " . $row['total_time'];
    } else {
        echo "No data found.";
    }
}

// Close connection
$conn->close();

?>
