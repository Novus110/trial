<?php
session_start();
require_once 'database_functions.php';

// 1. Get quiz results from POST data
$score = $_POST['score'];
$time_taken = $_POST['time']; // Time in seconds

// 2. Save to database
try {
    $stmt = $db->prepare("INSERT INTO quiz_scores (user_id, score, time_taken) VALUES (?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'], 
        $score, 
        $time_taken
    ]);
    
    // 3. Return success
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // 4. Error handling
    echo json_encode(['error' => 'Failed to save score']);
}
?>