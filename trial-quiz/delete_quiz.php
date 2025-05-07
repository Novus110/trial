<?php
// Database connection
$host = 'localhost';
$dbname = 'trial_quiz';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Get quiz ID from URL
$quizId = $_GET['id'] ?? null;

if (!$quizId) {
    die("Quiz ID not specified");
}

// Start transaction to ensure all related data is deleted properly
$pdo->beginTransaction();

try {
    // 1. Get all questions for this quiz
    $stmt = $pdo->prepare("SELECT id FROM questions WHERE quiz_id = ?");
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Delete options for each question
    foreach ($questions as $question) {
        $stmt = $pdo->prepare("DELETE FROM options WHERE question_id = ?");
        $stmt->execute([$question['id']]);
    }
    
    // 3. Delete all questions for this quiz
    $stmt = $pdo->prepare("DELETE FROM questions WHERE quiz_id = ?");
    $stmt->execute([$quizId]);
    
    // 4. Delete the quiz
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->execute([$quizId]);
    
    // Commit the transaction
    $pdo->commit();
    
    // Redirect back to quizzes page with success message
    header("Location: quizzes.php?deleted=1");
    exit;
    
} catch (Exception $e) {
    // Rollback the transaction if something went wrong
    $pdo->rollBack();
    die("Error deleting quiz: " . $e->getMessage());
}
?>
