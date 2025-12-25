<?php
// create_database.php - Run this once to create the database and table

// Database configuration
$servername = "localhost";
$username = "root"; // Change this to your database username
$password = ""; // Change this to your database password

// Create connection without database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS manvi_proposal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("manvi_proposal");

// Create table
$sql = "CREATE TABLE IF NOT EXISTS proposal_responses (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response VARCHAR(10) NOT NULL,
    girlfriend_name VARCHAR(50) NOT NULL,
    user_agent TEXT,
    timestamp DATETIME,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Table 'proposal_responses' created successfully or already exists<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Create additional table for statistics
$sql = "CREATE TABLE IF NOT EXISTS site_statistics (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_views INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    last_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Table 'site_statistics' created successfully or already exists<br>";
} else {
    echo "Error creating statistics table: " . $conn->error . "<br>";
}

// Insert initial statistics record if not exists
$sql = "INSERT IGNORE INTO site_statistics (id, page_views, unique_visitors) VALUES (1, 0, 0)";
if ($conn->query($sql) === TRUE) {
    echo "Initial statistics record created<br>";
}

// Create a view for summary
$sql = "CREATE OR REPLACE VIEW response_summary AS
        SELECT 
            response,
            COUNT(*) as total_responses,
            DATE(created_at) as response_date,
            COUNT(DISTINCT ip_address) as unique_ips
        FROM proposal_responses 
        GROUP BY response, DATE(created_at)";

if ($conn->query($sql) === TRUE) {
    echo "View 'response_summary' created successfully<br>";
} else {
    echo "Error creating view: " . $conn->error . "<br>";
}

// Create indexes for better performance
$indexes = [
    "CREATE INDEX idx_response ON proposal_responses(response)",
    "CREATE INDEX idx_created_at ON proposal_responses(created_at)",
    "CREATE INDEX idx_ip_address ON proposal_responses(ip_address)"
];

foreach ($indexes as $index_sql) {
    if ($conn->query($index_sql) === TRUE) {
        echo "Index created successfully<br>";
    } else {
        echo "Error creating index: " . $conn->error . "<br>";
    }
}

echo "<h2>Database Setup Complete!</h2>";
echo "<p>The database has been successfully set up.</p>";
echo "<p>You can now:</p>";
echo "<ol>";
echo "<li>Use the website to send proposals</li>";
echo "<li>View responses at <a href='view_responses.php'>view_responses.php</a></li>";
echo "<li>View statistics at <a href='view_statistics.php'>view_statistics.php</a></li>";
echo "</ol>";

$conn->close();
?>