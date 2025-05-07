<?php
session_start();

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

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login_stud.php");
    exit();
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $totalQuestions = count($questions);
    $results = [];
    $startTime = $_POST['start_time'] ?? time();
    
    foreach ($questions as $question) {
        $questionId = $question['id'];
        $userAnswer = $_POST['question_'.$questionId] ?? null;
        $correctAnswer = null;
        
        // Find correct answer
        foreach ($question['options'] as $option) {
            if ($option['is_correct']) {
                $correctAnswer = $option['id'];
                break;
            }
        }
        
        // Check if answer is correct
        $isCorrect = ($userAnswer == $correctAnswer);
        if ($isCorrect) {
            $score++;
        }
        
        $results[] = [
            'question' => $question['question_text'],
            'user_answer' => $userAnswer,
            'correct_answer' => $correctAnswer,
            'is_correct' => $isCorrect
        ];
    }
    
    // Calculate percentage and time taken
    $percentage = ($score / $totalQuestions) * 100;
    $timeTaken = time() - $startTime;
    
    // Save to leaderboard
    try {
        $stmt = $pdo->prepare("
            INSERT INTO quiz_scores 
            (user_id, quiz_id, score, time_taken) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $quizId,
            $score,
            $timeTaken
        ]);
        
        // Store results in session for display
        $_SESSION['quiz_results'] = [
            'score' => $score,
            'total' => $totalQuestions,
            'percentage' => $percentage,
            'time_taken' => $timeTaken,
            'results' => $results,
            'quiz_title' => $quiz['title']
        ];
        
        // Redirect to results page
        header("Location: quiz_results.php");
        exit();
    } catch (PDOException $e) {
        $saveError = "Your answers were recorded but couldn't save to leaderboard: " . $e->getMessage();
    }
}

function getOptionText($pdo, $optionId) {
    $stmt = $pdo->prepare("SELECT option_text FROM options WHERE id = ?");
    $stmt->execute([$optionId]);
    $option = $stmt->fetch(PDO::FETCH_ASSOC);
    return $option['option_text'] ?? 'Unknown option';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz: <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Comic Sans MS', cursive, sans-serif;
            background-image: url(IMG/teachers-dashboard-bg.png);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 20px;
        }
        .quiz-container {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .question-card {
            background-color: #fff9e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #ff9e7d;
        }
        .timer {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 1.2rem;
            z-index: 1000;
        }
        .btn-leaderboard {
            background-color: #FFD54F;
            border-color: #FFC107;
            color: #333;
            font-weight: bold;
        }
        .btn-leaderboard:hover {
            background-color: #FFC107;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($saveError)): ?>
            <div class="alert alert-warning"><?= $saveError ?></div>
        <?php endif; ?>
        
        <div class="quiz-container">
            <h1 class="text-center"><i class="fas fa-pencil-alt me-2"></i><?= htmlspecialchars($quiz['title']) ?></h1>
            
            <?php if (!isset($_POST['submit_quiz'])): ?>
                <div id="quiz-timer" class="timer">
                    <i class="fas fa-clock me-2"></i>
                    <span id="time-display">00:00</span>
                </div>
                
                <form method="POST" action="take_quiz.php?id=<?= $quizId ?>">
                    <input type="hidden" name="start_time" id="start-time" value="<?= time() ?>">
                    
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-card">
                            <div class="question-number">Question <?= $index + 1 ?></div>
                            <p class="lead"><?= htmlspecialchars($question['question_text']) ?></p>
                            
                            <div class="options">
                                <?php foreach ($question['options'] as $option): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" 
                                               name="question_<?= $question['id'] ?>" 
                                               id="option_<?= $option['id'] ?>" 
                                               value="<?= $option['id'] ?>" required>
                                        <label class="form-check-label" for="option_<?= $option['id'] ?>">
                                            <?= htmlspecialchars($option['option_text']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="submit_quiz" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Submit Quiz
                        </button>
                    </div>
                </form>
                
                <script>
                    // Timer functionality
                    document.addEventListener('DOMContentLoaded', function() {
                        const startTime = Math.floor(Date.now() / 1000);
                        document.getElementById('start-time').value = startTime;
                        
                        function updateTimer() {
                            const now = Math.floor(Date.now() / 1000);
                            const elapsed = now - startTime;
                            const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
                            const seconds = (elapsed % 60).toString().padStart(2, '0');
                            document.getElementById('time-display').textContent = `${minutes}:${seconds}`;
                        }
                        
                        setInterval(updateTimer, 1000);
                        updateTimer();
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>