<?php
session_start();

if (!isset($_SESSION['quiz_results'])) {
    header("Location: quizzes.php");
    exit();
}

$results = $_SESSION['quiz_results'];
unset($_SESSION['quiz_results']);

// Database connection (for leaderboard link)
$quizId = $_GET['quiz_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results: <?= htmlspecialchars($results['quiz_title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Comic Sans MS', cursive, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .results-container {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .score-display {
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
        .result-card {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .correct {
            background-color: #e6f7e6;
            border-left: 4px solid #2ecc71;
        }
        .incorrect {
            background-color: #ffebee;
            border-left: 4px solid #e74c3c;
        }
        .time-taken {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #666;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="results-container">
        <h1 class="text-center mb-4">
            <i class="fas fa-poll me-2"></i>
            Quiz Results: <?= htmlspecialchars($results['quiz_title']) ?>
        </h1>
        
        <div class="score-display text-<?= ($results['percentage'] >= 70) ? 'success' : 'danger' ?>">
            <?= $results['score'] ?>/<?= $results['total'] ?> 
            (<?= number_format($results['percentage'], 1) ?>%)
        </div>
        
        <div class="time-taken">
            <i class="fas fa-clock me-2"></i>
            Time taken: <?= gmdate("i:s", $results['time_taken']) ?> minutes
        </div>
        
        <h3 class="mb-3">Question Breakdown:</h3>
        
        <?php foreach ($results['results'] as $index => $result): ?>
            <div class="result-card <?= $result['is_correct'] ? 'correct' : 'incorrect' ?>">
                <h5>Question <?= $index + 1 ?>: <?= htmlspecialchars($result['question']) ?></h5>
                <p><strong>Your answer:</strong> 
                    <?php if ($result['user_answer']): ?>
                        <?= htmlspecialchars($result['user_answer']) ?>
                    <?php else: ?>
                        (No answer selected)
                    <?php endif; ?>
                    
                    <?php if ($result['is_correct']): ?>
                        <span class="text-success ms-2"><i class="fas fa-check"></i> Correct</span>
                    <?php else: ?>
                        <span class="text-danger ms-2"><i class="fas fa-times"></i> Incorrect</span>
                    <?php endif; ?>
                </p>
                
                <?php if (!$result['is_correct']): ?>
                    <p><strong>Correct answer:</strong> <?= htmlspecialchars($result['correct_answer']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="action-buttons">
            <?php if ($quizId): ?>
                <a href="leaderboard.php?quiz_id=<?= $quizId ?>" class="btn btn-warning btn-lg">
                    <i class="fas fa-trophy me-2"></i> View Leaderboard
                </a>
            <?php endif; ?>
            
            <a href="quizzes.php" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-left me-2"></i> Back to Quizzes
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>