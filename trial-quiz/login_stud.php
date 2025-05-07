<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Adventure Academy!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
        
        body {
            background: linear-gradient(135deg, #c2e9fb 0%, #a1c4fd 100%);
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        .rainbow-text {
            background-image: linear-gradient(to right, #ff8a00, #e52e71, #2196f3, #43a047);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: rainbow-animation 6s linear infinite;
            background-size: 400% 100%;
        }

        @keyframes rainbow-animation {
            0%, 100% {
                background-position: 0 0;
            }
            50% {
                background-position: 100% 0;
            }
        }

        .adventure-card {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 30px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            background-color: rgba(255, 255, 255, 0.95);
            position: relative;
            z-index: 10;
            border: 8px solid white;
            overflow: hidden;
        }

        .adventure-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 15px;
            background: linear-gradient(90deg, #FF9A8B, #FF6A88, #FF99AC, #FCB69F, #FF9A8B);
            background-size: 500% 100%;
            animation: gradientBorder 4s ease infinite;
            z-index: 1;
        }

        @keyframes gradientBorder {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .btn-adventure {
            width: 100%;
            padding: 15px;
            font-size: 1.2rem;
            margin: 15px 0;
            border-radius: 50px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
            font-weight: bold;
            letter-spacing: 1px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .btn-continue {
            background: linear-gradient(45deg, #4776E6, #8E54E9);
            border: none;
            color: white;
        }

        .btn-continue:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(71, 118, 230, 0.3);
        }

        .btn-create {
            background: linear-gradient(45deg, #56CCF2, #2F80ED);
            border: none;
            color: white;
        }

        .btn-create:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(47, 128, 237, 0.3);
        }

        .form-control {
            border-radius: 20px;
            border: 2px solid #e1e1e1;
            padding: 12px 20px;
            font-size: 1.1rem;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #8E54E9;
            box-shadow: 0 0 0 0.25rem rgba(142, 84, 233, 0.25);
        }

        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
            font-weight: 700;
            color: #333;
        }

        .section-title::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #FF9A8B, #FF6A88, #FF99AC);
            border-radius: 10px;
        }

        .welcome-icon {
            font-size: 2.5rem;
            margin-right: 10px;
            color: #FF6A88;
        }

        .divider {
            height: 3px;
            background: linear-gradient(90deg, transparent, #e1e1e1, transparent);
            margin: 25px 0;
            border-radius: 50%;
        }

        .star {
            position: absolute;
            background-color: #FFF;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.8));
            animation: twinkle 5s infinite ease-in-out;
        }

        @keyframes twinkle {
            0%, 100% {
                opacity: 0.2;
                transform: scale(0.5);
            }
            50% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .cloud {
            position: absolute;
            background-color: white;
            border-radius: 100px;
            height: 30px;
            width: 100px;
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.1));
            opacity: 0.8;
            z-index: 1;
        }

        .cloud:before, .cloud:after {
            content: '';
            background-color: white;
            position: absolute;
            border-radius: 50%;
        }

        .cloud:before {
            width: 50px;
            height: 50px;
            top: -20px;
            left: 10px;
        }

        .cloud:after {
            width: 40px;
            height: 40px;
            top: -15px;
            right: 15px;
        }

        .character {
            position: absolute;
            width: 100px;
            height: 100px;
            bottom: -20px;
            z-index: 2;
            transition: transform 0.3s ease;
        }

        .character-left {
            left: -50px;
        }

        .character-right {
            right: -50px;
            transform: scaleX(-1);
        }

        .bounce {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-15px);
            }
            60% {
                transform: translateY(-7px);
            }
        }

        @media (max-width: 768px) {
            .adventure-card {
                margin: 30px 15px;
                padding: 20px;
            }
            .character {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Background elements -->
    <div class="cloud" style="top: 15%; left: 10%;"></div>
    <div class="cloud" style="top: 35%; right: 15%;"></div>
    <div class="cloud" style="bottom: 25%; left: 18%;"></div>
    
    <!-- Stars -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            for (let i = 0; i < 30; i++) {
                const star = document.createElement('div');
                star.classList.add('star');
                star.style.left = `${Math.random() * 100}%`;
                star.style.top = `${Math.random() * 100}%`;
                star.style.animationDelay = `${Math.random() * 5}s`;
                document.body.appendChild(star);
            }
        });
    </script>

    <div class="container">
        <div class="adventure-card">
            

            <div class="text-center">
                <h1 class="mb-3 rainbow-text">Welcome to Adventure Academy!</h1>
                <div class="d-flex justify-content-center align-items-center">
                    <i class="fas fa-star welcome-icon bounce"></i>
                    <p class="lead mb-4" style="font-size: 1.3rem; color: #444;">Are you ready to start your learning adventure?</p>
                    <i class="fas fa-magic welcome-icon bounce" style="transform: rotate(30deg);"></i>
                </div>
            </div>
            
            <!-- Login Form (Returning Users) -->
            <div id="loginSection" class="mb-4 p-3" style="background-color: rgba(255,255,255,0.7); border-radius: 20px;">
                <h3 class="section-title">I'm Back for More Fun!</h3>
                <?php if ($error === 'not_found'): ?>
                    <div class="alert alert-danger">Username not found!</div>
                <?php endif; ?>
                <form action="authenticate.php" method="post">
                    <div class="input-group mb-3">
                        <span class="input-group-text" style="border-radius: 20px 0 0 20px; background: #f1f1f1;">
                            <i class="fas fa-user-astronaut text-primary"></i>
                        </span>
                        <input type="text" class="form-control" name="username" placeholder="Enter your username" required>
                    </div>
                    <button type="submit" class="btn btn-adventure btn-continue">
                        <i class="fas fa-rocket me-2"></i> Continue My Adventure!
                    </button>
                </form>
            </div>
            
            <div class="divider"></div>
            
            <!-- Create Button (New Users) -->
            <div class="p-3" style="background-color: rgba(255,255,255,0.7); border-radius: 20px;">
                <h3 class="section-title">I'm New Here!</h3>
                <p class="text-center mb-3" style="color: #555;">Create your own character and start exploring!</p>
                <a href="choose_char.php" class="btn btn-adventure btn-create">
                    <i class="fas fa-wand-magic-sparkles me-2"></i> Create My Character!
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>