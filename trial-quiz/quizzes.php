<?php
// Database connection
$host = 'localhost';
$dbname = 'trial_quiz';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if we're processing a delete request
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $quizId = $_GET['id'];
        
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
            $error_message = "Error deleting quiz: " . $e->getMessage();
        }
    }
    
    // If we reach here, we're displaying the quizzes list
    // Fetch all quizzes
    $stmt = $pdo->query("SELECT * FROM quizzes ORDER BY created_at DESC");
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kids Quizzes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #3a5b8a;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .quiz-card {
            background-color: #fff9e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #ff9e7d;
            transition: transform 0.3s;
        }
        
        .quiz-card:hover {
            transform: translateY(-5px);
        }
        
        .btn-create {
            background-color: #ff7eb9;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        .btn-create:hover {
            background-color: #ff65a3;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c82333;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="quiz-container">
            <h1><i class="fas fa-list-alt me-2"></i>Kids Quizzes</h1>
            
           
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="text-center">
                <a href="dashboard_stud.php" class="btn btn-create">
                    <i class="fa-solid fa-backward"></i> Back
                </a>
            </div>
            
            <div class="row">
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="col-md-6 mb-4">
                        <div class="quiz-card">
                            <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                            <p class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i>
                                <?= date('F j, Y', strtotime($quiz['created_at'])) ?>
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                
                                <a href="take_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-success">
                                    <i class="fas fa-play me-1"></i>Take Quiz
                                </a>
                              
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($quizzes)): ?>
                    <div class="col-12 text-center">
                        <p class="lead">No quizzes found. Create your first quiz!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
