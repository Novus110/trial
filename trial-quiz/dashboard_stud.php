<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: create_quiz.php");
    exit();
}

// Database connection for leaderboard
$host = 'localhost';
$dbname = 'trial_quiz';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch leaderboard data
    $leaderboardData = [
        'quizScores' => [],
        'matchingScores' => []
    ];
    
    // Get quiz scores
    $stmt = $pdo->prepare("
        SELECT u.username, u.avatar, qs.score, qs.time_taken 
        FROM quiz_scores qs
        JOIN users u ON qs.user_id = u.id
        ORDER BY qs.score DESC, qs.time_taken ASC
        LIMIT 5
    ");
    $stmt->execute();
    $leaderboardData['quizScores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get matching game scores
    $stmt = $pdo->prepare("
        SELECT u.username, u.avatar, ms.score, ms.time_taken 
        FROM matching_scores ms
        JOIN users u ON ms.user_id = u.id
        ORDER BY ms.score DESC, ms.time_taken ASC
        LIMIT 5
    ");
    $stmt->execute();
    $leaderboardData['matchingScores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Silently fail - leaderboard won't show but rest of dashboard works
    error_log("Leaderboard error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational App Interface</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        body {
            background-color: #FFD54F;
            font-family: 'Arial Rounded MT Bold', 'Arial', sans-serif;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="none" width="100" height="100"/><path fill="%23F9C74F" d="M20,20 L30,20 L30,30 L20,30 Z M40,40 L50,40 L50,50 L40,50 Z M60,60 L70,60 L70,70 L60,70 Z M80,20 L90,20 L90,30 L80,30 Z M20,80 L30,80 L30,90 L20,90 Z"/></svg>');
        }
        
        .navbar {
            background-color: #FFD54F;
            padding: 15px 20px;
            border-bottom: 2px solid rgba(0,0,0,0.1);
            width: 100%;
        }
        
        .back-button {
            background-color: #333;
            color: #FFD54F;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .profile-button {
            background-color: #333;
            color: #FFD54F;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .username {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        .parents-button {
            background-color: #66BB6A;
            color: #333;
            border-radius: 20px;
            padding: 5px 15px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        
        .content-card {
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .clickable-card {
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }
        
        .clickable-card:hover .content-card {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .card-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .card-body {
            padding: 20px;
            text-align: center;
        }
        
        .card-title {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .card-subtitle {
            color: #757575;
            font-size: 1.2rem;
        }
        
        .mission-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .mission-info {
            flex: 1;
        }
        
        .mission-rewards {
            display: flex;
            align-items: center;
        }
        
        .quiz-image {
            background-color: #4FC3F7;
        }
        
        .matching-image {
            background: linear-gradient(135deg, #FF5252 0%, #FF8A80 25%, #FFEB3B 50%, #69F0AE 75%, #40C4FF 100%);
        }
        
        .content-area {
            padding: 20px;
        }
        
        .icon-container {
            transform: scale(1.5);
        }
        
        /* Leaderboard Styles */
        .leaderboard-section {
            margin-top: 30px;
            background-color: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .leaderboard-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .leaderboard-table th {
            background-color: #FFD54F;
            color: #333;
            padding: 12px;
            text-align: left;
        }
        
        .leaderboard-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .leaderboard-table tr:last-child td {
            border-bottom: none;
        }
        
        .leaderboard-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid #FFD54F;
        }
        
        .leaderboard-player {
            display: flex;
            align-items: center;
        }
        
        .leaderboard-position {
            font-weight: bold;
            color: #FFD54F;
            width: 30px;
            display: inline-block;
            text-align: center;
        }
        
        .position-1 { color: #FFD700; }
        .position-2 { color: #C0C0C0; }
        .position-3 { color: #CD7F32; }
        
        .refresh-btn {
            background-color: #333;
            color: #FFD54F;
            border: none;
            border-radius: 20px;
            padding: 5px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        
        .refresh-btn i {
            margin-right: 5px;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #FFD54F;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            color: #757575;
        }
        
        .tab.active {
            color: #333;
            border-bottom: 3px solid #333;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .current-user {
            background-color: rgba(255, 213, 79, 0.3);
            font-weight: bold;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar {
                padding: 10px 15px;
            }
            
            .username {
                font-size: 1.2rem;
            }
            
            .parents-button {
                padding: 3px 10px;
                font-size: 0.9rem;
            }
            
            .card-image {
                height: 200px;
            }
            
            .card-title {
                font-size: 1.5rem;
            }
            
            .card-subtitle {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar {
                flex-wrap: wrap;
            }
            
            .card-image {
                height: 180px;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                width: 100%;
                text-align: center;
            }
        }
        
        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid #333;
        }
    </style>
</head>
<body>
    <div class="navbar d-flex align-items-center">
        <div class="profile-button">
            <img src="IMG/<?php echo htmlspecialchars($_SESSION['avatar']); ?>.png" alt="Avatar" class="profile-avatar">
        </div>
        <div class="username me-auto"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
        
        <a href="logout_stud.php" class="parents-button me-2">
            LOGOUT
        </a>
    </div>
    
    <div class="content-area">
        <div class="container-fluid">
            <div class="row g-4">
                <!-- Quiz Card - Now Clickable -->
                <div class="col-md-6 mb-4">
                    <a href="quizzes.php" class="clickable-card" onclick="return checkPhpSupport()">
                        <div class="content-card">
                            <div class="quiz-image card-image d-flex align-items-center justify-content-center">
                                <div class="d-flex flex-column align-items-center icon-container">
                                    <i class="fas fa-question-circle fa-4x mb-4" style="color: white;"></i>
                                    <div class="d-flex">
                                        <i class="fas fa-lightbulb fa-2x me-3" style="color: #FFD700;"></i>
                                        <i class="fas fa-brain fa-2x me-3" style="color: white;"></i>
                                        <i class="fas fa-check-circle fa-2x" style="color: #4CAF50;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-title">QUIZ</div>
                                <div class="card-subtitle">Test your knowledge!</div>
                            </div>
                        </div>
                    </a>
                </div>
                
                <!-- Matching Card - Now Clickable -->
                <div class="col-md-6 mb-4">
                    <a href="matching-cards.html" class="clickable-card">
                        <div class="content-card">
                            <div class="matching-image card-image d-flex align-items-center justify-content-center">
                                <div class="d-flex flex-column align-items-center icon-container">
                                    <i class="fas fa-puzzle-piece fa-4x mb-4" style="color: white;"></i>
                                    <div class="d-flex">
                                        <i class="fas fa-link fa-2x me-3" style="color: white;"></i>
                                        <i class="fas fa-object-group fa-2x me-3" style="color: white;"></i>
                                        <i class="fas fa-equals fa-2x" style="color: white;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-title">MATCHING</div>
                                <div class="card-subtitle">Find the matching pairs!</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Leaderboard Section -->
            <div class="row">
                <div class="col-12">
                    <div class="leaderboard-section">
                        <div class="leaderboard-title">
                            <span>LEADERBOARDS</span>
                            <button class="refresh-btn" id="refresh-leaderboard">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                        
                        <div class="tabs">
                            <div class="tab active" data-tab="quiz">Quiz Scores</div>
                            <div class="tab" data-tab="matching">Matching Game</div>
                        </div>
                        
                        <div class="tab-content active" id="quiz-leaderboard">
                            <table class="leaderboard-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Player</th>
                                        <th>Score</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($leaderboardData['quizScores'])): ?>
                                        <?php foreach ($leaderboardData['quizScores'] as $index => $player): ?>
                                            <tr class="<?= ($player['username'] === $_SESSION['username']) ? 'current-user' : '' ?>">
                                                <td><span class="leaderboard-position position-<?= $index + 1 ?>"><?= $index + 1 ?></span></td>
                                                <td>
                                                    <div class="leaderboard-player">
                                                        <img src="IMG/<?= htmlspecialchars($player['avatar'] ?? 'default') ?>.png" 
                                                             alt="<?= htmlspecialchars($player['username']) ?>" 
                                                             class="leaderboard-avatar">
                                                        <?= htmlspecialchars($player['username']) ?>
                                                    </div>
                                                </td>
                                                <td><?= $player['score'] ?></td>
                                                <td><?= $player['time_taken'] ?>s</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center;">No quiz scores yet!</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="tab-content" id="matching-leaderboard">
                            <table class="leaderboard-table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Player</th>
                                        <th>Score</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($leaderboardData['matchingScores'])): ?>
                                        <?php foreach ($leaderboardData['matchingScores'] as $index => $player): ?>
                                            <tr class="<?= ($player['username'] === $_SESSION['username']) ? 'current-user' : '' ?>">
                                                <td><span class="leaderboard-position position-<?= $index + 1 ?>"><?= $index + 1 ?></span></td>
                                                <td>
                                                    <div class="leaderboard-player">
                                                        <img src="IMG/<?= htmlspecialchars($player['avatar'] ?? 'default') ?>.png" 
                                                             alt="<?= htmlspecialchars($player['username']) ?>" 
                                                             class="leaderboard-avatar">
                                                        <?= htmlspecialchars($player['username']) ?>
                                                    </div>
                                                </td>
                                                <td><?= $player['score'] ?></td>
                                                <td><?= $player['time_taken'] ?>s</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center;">No matching game scores yet!</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Function to check if PHP is supported (for links to PHP pages)
    function checkPhpSupport() {
        // Check if we're running on a local file protocol
        if (window.location.protocol === 'file:') {
            alert('PHP files cannot be executed locally. Please upload to a web server with PHP support.');
            return false; // Prevent navigation
        }
        return true; // Allow navigation
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and content
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab') + '-leaderboard';
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Refresh leaderboard
        document.getElementById('refresh-leaderboard').addEventListener('click', function() {
            const btn = this;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            
            fetch('get_leaderboard.php?limit=5')
                .then(response => response.json())
                .then(data => {
                    updateLeaderboard('quiz', data.quizScores);
                    updateLeaderboard('matching', data.matchingScores);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to refresh leaderboard');
                })
                .finally(() => {
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
                });
        });
        
        function updateLeaderboard(type, data) {
            const tbody = document.querySelector(`#${type}-leaderboard tbody`);
            tbody.innerHTML = '';
            
            if (data && data.length > 0) {
                data.forEach((player, index) => {
                    const row = document.createElement('tr');
                    if (player.username === '<?= $_SESSION['username'] ?>') {
                        row.classList.add('current-user');
                    }
                    row.innerHTML = `
                        <td><span class="leaderboard-position position-${index + 1}">${index + 1}</span></td>
                        <td>
                            <div class="leaderboard-player">
                                <img src="IMG/${player.avatar || 'default'}.png" 
                                     alt="${player.username}" 
                                     class="leaderboard-avatar">
                                ${player.username}
                            </div>
                        </td>
                        <td>${player.score}</td>
                        <td>${player.time_taken}s</td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No scores yet!</td></tr>';
            }
        }
        
        // Profile button functionality - shows user info
        document.querySelector('.profile-button').addEventListener('click', function() {
            alert(`User Profile:\nName: <?= addslashes($_SESSION['username']) ?>\nAvatar: <?= ucfirst(str_replace(['boy', 'girl'], ['Boy ', 'Girl '], $_SESSION['avatar'])) ?>`);
        });
        
        // Add click effects to all cards
        const cards = document.querySelectorAll('.clickable-card');
        cards.forEach(card => {
            // Visual feedback when clicked
            card.addEventListener('mousedown', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            card.addEventListener('mouseup', function() {
                this.style.transform = '';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
            
            // Special handling for PHP links
            const href = card.getAttribute('href');
            if (href && href.includes('.php')) {
                card.addEventListener('click', function(e) {
                    if (!checkPhpSupport()) {
                        e.preventDefault();
                    }
                });
            }
        });
        
        // Animation for trophy icons
        const trophies = document.querySelectorAll('.fa-trophy, .fa-medal, .fa-award');
        trophies.forEach((trophy, index) => {
            trophy.style.transform = 'translateY(0)';
            trophy.style.transition = 'transform 0.3s ease';
            
            // Add slight bounce animation on hover
            trophy.addEventListener('mouseenter', function() {
                this.style.transform = `translateY(${-5 + (index * 2)}px)`;
            });
            
            trophy.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
    </script>
</body>
</html>