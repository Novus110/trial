<?php
header('Content-Type: application/json');

// Database connection
$config = [
    'host' => 'localhost',
    'dbname' => 'kids_avatar_system',
    'username' => 'root',
    'password' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_avatars':
        getAvatars($pdo);
        break;
    case 'save_player':
        savePlayer($pdo);
        break;
    case 'check_username':
        checkUsername($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getAvatars($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM avatars WHERE is_active = TRUE");
        echo json_encode([
            'success' => true,
            'avatars' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch avatars']);
    }
}

function savePlayer($pdo) {
    $username = trim($_POST['username'] ?? '');
    $avatarPath = $_POST['avatar_path'] ?? '';
    
    // Validate inputs
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Please choose a player name']);
        return;
    }
    
    if (strlen($username) > 20) {
        echo json_encode(['success' => false, 'message' => 'Name must be 20 characters or less']);
        return;
    }
    
    if (empty($avatarPath)) {
        echo json_encode(['success' => false, 'message' => 'Please select an avatar']);
        return;
    }
    
    try {
        // Check if username exists first
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM players WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'This name is already taken. Please choose another.']);
            return;
        }
        
        // Save new player
        $stmt = $pdo->prepare("INSERT INTO players (username, avatar_path) VALUES (?, ?)");
        $stmt->execute([$username, $avatarPath]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Welcome, ' . htmlspecialchars($username) . '! Your character is ready!',
            'player_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error saving player: ' . $e->getMessage()]);
    }
}

function checkUsername($pdo) {
    $username = trim($_POST['username'] ?? '');
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a name']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM players WHERE username = ?");
        $stmt->execute([$username]);
        $exists = $stmt->fetchColumn() > 0;
        
        echo json_encode([
            'success' => true,
            'available' => !$exists,
            'message' => $exists ? 'This name is taken' : 'Name is available!'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error checking name']);
    }
}
?>