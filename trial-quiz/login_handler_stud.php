<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "educational_app";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get username from form
$inputUsername = trim($_POST['username']);

// Check if username exists
$stmt = $conn->prepare("SELECT username, avatar FROM users WHERE username = ?");
$stmt->bind_param("s", $inputUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists - log them in
    $user = $result->fetch_assoc();
    $_SESSION['username'] = $user['username'];
    $_SESSION['avatar'] = $user['avatar'];
    header("Location: main_page.php");
    exit();
} else {
    // User doesn't exist
    header("Location: dashboard_stud.php?error=usernotfound");
    exit();
}

$stmt->close();
$conn->close();
?>