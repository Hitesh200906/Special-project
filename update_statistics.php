<?php
// update_statistics.php - Update page view statistics

require_once 'config.php';

// Track this page view
trackPageView();

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Statistics updated']);
?>