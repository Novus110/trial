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

// Fetch quiz details
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die("Quiz not found");
}

// Fetch questions for this quiz
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch options for each question
foreach ($questions as &$question) {
    $stmt = $pdo->prepare("SELECT * FROM options WHERE question_id = ?");
    $stmt->execute([$question['id']]);
    $question['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($question); // Break the reference
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['title']) ?> - Kids Quiz</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Comic Sans MS', cursive, sans-serif;
            background-image: url('IMG/webpage-bg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 20px;
        }
        
        .quiz-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #3a5b8a;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .question-card {
            background-color: #fff9e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #ff9e7d;
        }
        
        .question-number {
            font-weight: bold;
            color: #3a5b8a;
            margin-bottom: 10px;
        }
        
        .option {
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        
        .correct-option {
            background-color: #e6f7e6;
            border-left: 3px solid #2ecc71;
        }
        
        .btn-back {
            background-color: #3a5b8a;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 18px;
            margin-top: 20px;
        }
        
        .btn-back:hover {
            background-color: #2c4a6e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="quiz-container">
            <h1><i class="fas fa-question-circle me-2"></i><?= htmlspecialchars($quiz['title']) ?></h1>
            
            <div id="quizQuestions">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card">
                        <div class="question-number">Question <?= $index + 1 ?></div>
                        <p class="lead"><?= htmlspecialchars($question['question_text']) ?></p>
                        
                        <div class="options">
                            <?php foreach ($question['options'] as $option): ?>
                                <div class="option <?= $option['is_correct'] ? 'correct-option' : '' ?>">
                                    <?= htmlspecialchars($option['option_text']) ?>
                                    <?php if ($option['is_correct']): ?>
                                        <i class="fas fa-check-circle ms-2 text-success"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="create_quiz.php" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Create Another Quiz
                </a>
                <a href="dashboard.php" class="btn btn-back mt-3">
                    <i class="fas fa-list me-2"></i>View All Quizzes
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
