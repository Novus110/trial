<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $avatar = $_POST['avatar'];

    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: choose_char.php?error=username_taken");
            exit();
        }

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, avatar) VALUES (?, ?)");
        $stmt->execute([$username, $avatar]);
        
        // Start session and redirect to dashboard
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['avatar'] = $avatar;
        
        header("Location: dashboard_stud.php");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: choose_chaar.php");
    exit();
}
?>