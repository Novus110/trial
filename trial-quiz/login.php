<?php
session_start();

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

// Process login form
$login_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $login_error = 'Both username and password are required';
    } else {
        // Check user credentials
        $stmt = $conn->prepare("SELECT id, username, password, full_name FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['logged_in'] = true;
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $login_error = 'Invalid username or password';
            }
        } else {
            $login_error = 'Invalid username or password';
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Comic Sans MS', cursive, sans-serif;
            background-image: url('IMG/webpage-bg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background-color: white;
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 4px solid #ff9e7d;
        }
        
        h1 {
            color: #3a5b8a;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #3a5b8a;
            font-weight: bold;
        }
        
        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #ccc;
            font-family: inherit;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: #ff7eb9;
            box-shadow: 0 0 0 0.25rem rgba(255, 126, 185, 0.25);
        }
        
        .btn-primary {
            background-color: #ff7eb9;
            border: none;
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 18px;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .btn-primary:hover {
            background-color: #ff65a3;
        }
        
        .btn-secondary {
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 18px;
            width: 100%;
            margin-top: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-link a {
            color: #ff7eb9;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            margin-bottom: 20px;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.8rem;
                margin-bottom: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
        <h1>Teacher Login</h1>
        
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Registration successful! Please log in.</div>
        <?php endif; ?>
        
        <?php if (!empty($login_error)): ?>
            <div class="alert alert-danger"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
        <form id="loginForm" method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="btn btn-primary" id="loginBtn">Login</button>
                <button type="button" class="btn btn-secondary" id="togoback">Back</button>
            </div>
        </form>
        
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('togoback').addEventListener('click', function() {
            window.location.href = 'choose-area.html';
        });

        // Add form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // Get the login button
            const loginBtn = document.getElementById('loginBtn');
            
            // Show loading state
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';
            loginBtn.disabled = true;
            
            // You could add client-side validation here if needed
            // For example:
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault(); // Prevent form submission
                alert('Please fill in all fields');
                loginBtn.innerHTML = 'Login';
                loginBtn.disabled = false;
                return false;
            }
            
            // If validation passes, the form will submit normally
            // The PHP code will handle the server-side validation
            return true;
        });

        // Clear loading state if form submission fails (though this would typically only happen if JavaScript is disabled)
        window.addEventListener('pageshow', function() {
            const loginBtn = document.getElementById('loginBtn');
            if (loginBtn) {
                loginBtn.innerHTML = 'Login';
                loginBtn.disabled = false;
            }
        });
    </script>
</body>
</html>