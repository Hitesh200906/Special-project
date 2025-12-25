<?php
// save_response.php - Save proposal responses to database

// Database configuration
$servername = "localhost";
$username = "root"; // Change this to your database username
$password = ""; // Change this to your database password
$dbname = "manvi_proposal"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Return JSON error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $conn->connect_error
    ]);
    exit();
}

// Create table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS proposal_responses (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response VARCHAR(10) NOT NULL,
    girlfriend_name VARCHAR(50) NOT NULL,
    user_agent TEXT,
    timestamp DATETIME,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createTableSQL)) {
    // Log error but continue
    error_log("Table creation failed: " . $conn->error);
}

// Get form data
$response = isset($_POST['response']) ? $_POST['response'] : '';
$girlfriend_name = isset($_POST['girlfriend_name']) ? $_POST['girlfriend_name'] : '';
$user_agent = isset($_POST['user_agent']) ? $_POST['user_agent'] : '';
$timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : date('Y-m-d H:i:s');
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

// Validate response
if (!in_array($response, ['yes', 'no'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid response type. Must be "yes" or "no".'
    ]);
    exit();
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO proposal_responses (response, girlfriend_name, user_agent, timestamp, ip_address) VALUES (?, ?, ?, ?, ?)");

if ($stmt === false) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to prepare statement',
        'error' => $conn->error
    ]);
    exit();
}

$stmt->bind_param("sssss", $response, $girlfriend_name, $user_agent, $timestamp, $ip_address);

// Execute the statement
if ($stmt->execute()) {
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Response saved successfully!',
        'data' => [
            'response' => $response,
            'girlfriend_name' => $girlfriend_name,
            'timestamp' => $timestamp
        ]
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save response',
        'error' => $stmt->error
    ]);
}

// Close connections
$stmt->close();
$conn->close();
?>