<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'teacher_app';

// Connect to database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
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

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
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

        .navbar {
            background-color: #ff7eb9;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: white;
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

        .logout-btn {
            background-color: #3a5b8a;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
        }
        
        .logout-btn:hover {
            background-color: #2c476b;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Teacher Dashboard</a>
            <div class="ms-auto d-flex align-items-center">
                <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($user['full_name']); ?></span>
                <a href="logout.php" class="btn btn-sm logout-btn">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="quiz-container">
            <h1><i class="fas fa-list-alt me-2"></i>Kids Quizzes</h1>
            
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Quiz was successfully deleted!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="text-center">
                <a href="create_quiz.php" class="btn btn-create">
                    <i class="fas fa-plus me-2"></i>Create New Quiz
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
                                <a href="view_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>View Quiz
                                </a>
                                <a href="take_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-success">
                                    <i class="fas fa-play me-1"></i>Take Quiz
                                </a>
                                <button class="btn btn-delete" 
                                        onclick="confirmDelete(<?= $quiz['id'] ?>, '<?= htmlspecialchars(addslashes($quiz['title'])) ?>')">
                                    <i class="fas fa-trash-alt me-1"></i>Delete
                                </button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <!-- Delete Confirmation Script -->
    <script>
        function confirmDelete(quizId, quizTitle) {
            if (confirm(`Are you sure you want to delete the quiz "${quizTitle}"? This action cannot be undone.`)) {
                window.location.href = `quizzes.php?action=delete&id=${quizId}`;
            }
        }
        
    </script>
</body>
</html>
