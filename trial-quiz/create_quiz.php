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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_quiz'])) {
    $quizTitle = $_POST['quiz_title'] ?? 'Untitled Quiz';
    $questions = $_POST['questions'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        // Insert quiz
        $stmt = $pdo->prepare("INSERT INTO quizzes (title) VALUES (?)");
        $stmt->execute([$quizTitle]);
        $quizId = $pdo->lastInsertId();
        
        // Insert questions and options
        foreach ($questions as $question) {
            $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (?, ?)");
            $stmt->execute([$quizId, $question['text']]);
            $questionId = $pdo->lastInsertId();
            
            foreach ($question['options'] as $index => $optionText) {
                $isCorrect = ($index == $question['correct_answer']);
                $stmt = $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                $stmt->execute([$questionId, $optionText, $isCorrect]);
            }
        }
        
        $pdo->commit();
        header("Location: view_quiz.php?id=$quizId");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error saving quiz: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kids Quiz Maker</title>
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
        
        .answer-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .correct-answer {
            background-color: #e6f7e6;
            border-left: 3px solid #2ecc71;
        }
        
        .btn-add {
            background-color: #ff7eb9;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .btn-add:hover {
            background-color: #ff65a3;
        }
        
        .btn-save {
            background-color: #3a5b8a;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 18px;
            margin-top: 20px;
        }
        
        .btn-save:hover {
            background-color: #2c4a6e;
        }
        
        .btn-remove {
            color: #e74c3c;
            background: none;
            border: none;
            font-size: 18px;
        }
        
        @media (max-width: 768px) {
            .quiz-container {
                padding: 20px;
            }
            
            .question-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="quiz-container">
            <h1><i class="fas fa-pencil-alt me-2"></i>Kids Quiz Maker</h1>
            
            <form id="quizForm" method="POST" action="create_quiz.php">
                <div class="mb-3">
                    <label for="quiz_title" class="form-label">Quiz Title</label>
                    <input type="text" class="form-control" id="quiz_title" name="quiz_title" required>
                </div>
                
                <div id="questionsContainer">
                    <!-- Questions will be added here dynamically -->
                </div>
                
                <div class="text-center">
                    <button type="button" class="btn btn-add" id="addQuestionBtn">
                        <i class="fas fa-plus me-2"></i>Add Question
                    </button>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-save" name="save_quiz" id="saveQuizBtn">
                        <i class="fas fa-save me-2"></i>Save Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questionsContainer = document.getElementById('questionsContainer');
            const addQuestionBtn = document.getElementById('addQuestionBtn');
            const saveQuizBtn = document.getElementById('saveQuizBtn');
            let questionCount = 0;
            
            // Add first question when page loads
            addQuestion();
            
            // Add question button click handler
            addQuestionBtn.addEventListener('click', addQuestion);
            
            // Function to add a new question
            function addQuestion() {
                questionCount++;
                const questionId = `question-${questionCount}`;
                
                const questionCard = document.createElement('div');
                questionCard.className = 'question-card';
                questionCard.id = questionId;
                questionCard.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="question-number">Question ${questionCount}</div>
                        <button type="button" class="btn-remove" onclick="removeQuestion('${questionId}')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="mb-3">
                        <label for="${questionId}-text" class="form-label">Question Text</label>
                        <input type="text" class="form-control" name="questions[${questionCount-1}][text]" id="${questionId}-text" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer Options</label>
                        <div id="${questionId}-options">
                            <div class="answer-option mb-2">
                                <input type="radio" name="questions[${questionCount-1}][correct_answer]" value="0" class="me-2" required>
                                <input type="text" class="form-control option-input" name="questions[${questionCount-1}][options][]" placeholder="Option 1" required>
                            </div>
                            <div class="answer-option mb-2">
                                <input type="radio" name="questions[${questionCount-1}][correct_answer]" value="1" class="me-2">
                                <input type="text" class="form-control option-input" name="questions[${questionCount-1}][options][]" placeholder="Option 2" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption('${questionId}-options', ${questionCount-1})">
                            <i class="fas fa-plus me-1"></i>Add Option
                        </button>
                    </div>
                `;
                
                questionsContainer.appendChild(questionCard);
                
                // Add event listeners for correct answer selection
                const radioButtons = questionCard.querySelectorAll(`input[name="questions[${questionCount-1}][correct_answer]"]`);
                radioButtons.forEach(radio => {
                    radio.addEventListener('change', function() {
                        const optionDiv = this.closest('.answer-option');
                        document.querySelectorAll(`#${questionId}-options .answer-option`).forEach(div => {
                            div.classList.remove('correct-answer');
                        });
                        optionDiv.classList.add('correct-answer');
                    });
                });
            }
            
            // Function to add an answer option to a question
            window.addOption = function(optionsContainerId, questionIndex) {
                const optionsContainer = document.getElementById(optionsContainerId);
                const optionCount = optionsContainer.querySelectorAll('.answer-option').length;
                
                const optionDiv = document.createElement('div');
                optionDiv.className = 'answer-option mb-2';
                optionDiv.innerHTML = `
                    <input type="radio" name="questions[${questionIndex}][correct_answer]" 
                           value="${optionCount}" class="me-2">
                    <input type="text" class="form-control option-input" name="questions[${questionIndex}][options][]" placeholder="Option ${optionCount + 1}" required>
                `;
                
                optionsContainer.appendChild(optionDiv);
                
                // Add event listener for correct answer selection
                const radioButton = optionDiv.querySelector('input[type="radio"]');
                radioButton.addEventListener('change', function() {
                    const parentDiv = this.closest('.answer-option');
                    document.querySelectorAll(`#${optionsContainerId} .answer-option`).forEach(div => {
                        div.classList.remove('correct-answer');
                    });
                    parentDiv.classList.add('correct-answer');
                });
            }
            
            // Function to remove a question
            window.removeQuestion = function(questionId) {
                if (confirm('Are you sure you want to remove this question?')) {
                    const questionCard = document.getElementById(questionId);
                    questionCard.remove();
                    
                    // Update question numbers
                    const questions = document.querySelectorAll('.question-card');
                    questions.forEach((question, index) => {
                        question.querySelector('.question-number').textContent = `Question ${index + 1}`;
                    });
                    
                    questionCount = questions.length;
                }
            }
        });
    </script>
</body>
</html>