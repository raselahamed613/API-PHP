from flask import Flask, render_template, request
import mysql.connector
import RPi.GPIO as GPIO

# GPIO setup
MOTOR_PIN = 27  # Change to your motor GPIO pin
GPIO.setmode(GPIO.BCM)
GPIO.setup(MOTOR_PIN, GPIO.OUT)

app = Flask(__name__)

# Database connection details
db_config = {
    'host': 'localhost',      # Update if hosted remotely
    'user': 'root',           # Your database username
    'password': 'Rasel@24',           # Your database password
    'database': 'pump_data'   # Your database name
}

# Function to fetch the latest midLowSensor state
def get_mid_low_sensor_state():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        query = "SELECT midLowSensor FROM sensor_data ORDER BY id DESC LIMIT 1"
        cursor.execute(query)
        result = cursor.fetchone()
        conn.close()
        return int(result['midLowSensor']) if result else None
    except Exception as e:
        print(f"Database error: {e}")
        return None

@app.route('/')
def index():
    sensor_state = get_mid_low_sensor_state()
    motor_status = GPIO.input(MOTOR_PIN)
    return render_template('index.html', sensor_state=sensor_state, motor_status=motor_status)

@app.route('/control', methods=['POST'])
def control_motor():
    if request.form['action'] == 'ON':
        sensor_state = get_mid_low_sensor_state()
        if sensor_state == 0:  # Check if midLowSensor is LOW (0)
            GPIO.output(MOTOR_PIN, GPIO.HIGH)  # Turn motor ON
    elif request.form['action'] == 'OFF':
        GPIO.output(MOTOR_PIN, GPIO.LOW)  # Turn motor OFF
    return "OK", 200

if __name__ == '__main__':
    try:
        app.run(host='0.0.0.0', port=80, debug=True)
    except KeyboardInterrupt:
        GPIO.cleanup()
