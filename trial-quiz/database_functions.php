<?php
require_once 'db_config.php';

function getLeaderboardData(int $limit = 10): array {
    // Validate limit
    $limit = max(1, min(100, $limit)); // Ensure limit is between 1-100

    $config = require 'db_config.php';
    $quiz_conn = $char_conn = null;
    
    try {
        // Connect to quiz database
        $quiz_dsn = "mysql:host={$config['quiz']['host']};dbname={$config['quiz']['dbname']};charset=utf8mb4";
        $quiz_conn = new PDO($quiz_dsn, $config['quiz']['user'], $config['quiz']['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Connect to character database
        $char_dsn = "mysql:host={$config['character_creator']['host']};dbname={$config['character_creator']['dbname']};charset=utf8mb4";
        $char_conn = new PDO($char_dsn, $config['character_creator']['user'], $config['character_creator']['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Fetch quiz scores
        $quizQuery = "SELECT u.username, u.avatar, qs.score, qs.time_taken
                     FROM quiz_scores qs
                     JOIN {$config['character_creator']['dbname']}.users u ON qs.user_id = u.id
                     ORDER BY qs.score DESC, qs.time_taken ASC
                     LIMIT :limit";
        
        $stmt = $quiz_conn->prepare($quizQuery);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $quizScores = $stmt->fetchAll();

        // Fetch matching game scores (FIXED: using char_conn)
        $matchingQuery = "SELECT u.username, u.avatar, ms.score, ms.time_taken
                         FROM matching_scores ms
                         JOIN users u ON ms.user_id = u.id
                         ORDER BY ms.score DESC, ms.time_taken ASC
                         LIMIT :limit";
        
        $stmt = $char_conn->prepare($matchingQuery);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $matchingScores = $stmt->fetchAll();

        return [
            'quizScores' => $quizScores,
            'matchingScores' => $matchingScores
        ];

    } catch (PDOException $e) {
        error_log('Leaderboard Error: ' . $e->getMessage());
        return ['error' => 'Failed to retrieve leaderboard data'];
    } finally {
        // Close connections
        $quiz_conn = null;
        $char_conn = null;
    }
}
?>