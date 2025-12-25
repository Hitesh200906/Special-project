<?php
// config.php - Database configuration

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change this
define('DB_PASS', ''); // Change this
define('DB_NAME', 'manvi_proposal');

// Create connection
function getDatabaseConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return null;
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Initialize database (run once)
function initializeDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        return false;
    }
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->query($sql);
    
    // Select database
    $conn->select_db(DB_NAME);
    
    // Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS proposal_responses (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            response VARCHAR(10) NOT NULL,
            girlfriend_name VARCHAR(50) NOT NULL,
            user_agent TEXT,
            timestamp DATETIME,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS site_statistics (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_views INT DEFAULT 0,
            unique_visitors INT DEFAULT 0,
            last_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables as $table_sql) {
        $conn->query($table_sql);
    }
    
    // Insert initial statistics
    $conn->query("INSERT IGNORE INTO site_statistics (id, page_views, unique_visitors) VALUES (1, 0, 0)");
    
    $conn->close();
    return true;
}

// Track page view
function trackPageView() {
    $conn = getDatabaseConnection();
    if (!$conn) return;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Check if this IP has visited before
    $check_sql = "SELECT id FROM proposal_responses WHERE ip_address = ? LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $ip);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    $is_unique = ($check_stmt->num_rows == 0);
    $check_stmt->close();
    
    // Update statistics
    $update_sql = "UPDATE site_statistics SET page_views = page_views + 1";
    if ($is_unique) {
        $update_sql .= ", unique_visitors = unique_visitors + 1";
    }
    $update_sql .= ", last_visit = NOW() WHERE id = 1";
    
    $conn->query($update_sql);
    $conn->close();
}
?>