<?php
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

// Process registration form
$registration_success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Username already exists';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $full_name);
            
            if ($stmt->execute()) {
                $registration_success = true;
                // Redirect to login after successful registration
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
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
    <title>Teacher Registration</title>
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
        
        .registration-container {
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
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #ff7eb9;
            text-decoration: none;
            font-weight: bold;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            margin-bottom: 20px;
        }
        
        @media (max-width: 576px) {
            .registration-container {
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
<div class="registration-container">
        <h1>Teacher Registration</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form id="registrationForm" method="POST" action="register.php">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="btn btn-primary" id="registerBtn">Register</button>
                <button type="button" class="btn btn-secondary" id="togoback">Back</button>
            </div>
        </form>
        
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Log in here</a></p>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('togoback').addEventListener('click', function() {
            window.location.href = 'choose-area.html';
        });

        // Add form submission handling for registration
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            // Get the register button
            const registerBtn = document.getElementById('registerBtn');
            
            // Show loading state
            registerBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registering...';
            registerBtn.disabled = true;
            
            // Client-side validation
            const fullName = document.getElementById('full_name').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check for empty fields
            if (!fullName || !username || !password || !confirmPassword) {
                e.preventDefault();
                alert('Please fill in all fields');
                resetRegisterButton();
                return false;
            }
            
            // Check password match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                resetRegisterButton();
                return false;
            }
            
            // Check password strength (optional)
            if (password.length < 8) {
                e.preventDefault();
                alert('Password should be at least 8 characters long');
                resetRegisterButton();
                return false;
            }
            
            // If validation passes, the form will submit normally
            return true;
        });

        // Function to reset register button state
        function resetRegisterButton() {
            const registerBtn = document.getElementById('registerBtn');
            registerBtn.innerHTML = 'Register';
            registerBtn.disabled = false;
        }

        // Clear loading state if form submission fails
        window.addEventListener('pageshow', function() {
            resetRegisterButton();
        });
    </script>
</body>
</html>