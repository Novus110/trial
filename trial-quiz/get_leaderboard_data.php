<?php
require_once 'database_functions.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Enable CORS if needed
header('Cache-Control: max-age=60'); // Cache response for 1 minute

try {
    // Get and validate limit (if provided)
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 10;
    
    // Fetch data
    $leaderboardData = getLeaderboardData($limit);
    
    // Handle errors from getLeaderboardData()
    if (isset($leaderboardData['error'])) {
        http_response_code(500); // Internal Server Error
    }
    
    // Output JSON
    echo json_encode($leaderboardData);
} catch (Exception $e) {
    // Handle unexpected errors
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>