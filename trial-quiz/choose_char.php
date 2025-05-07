<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Character</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
        
        body {
            background: linear-gradient(135deg, #c2e9fb 0%, #a1c4fd 100%);
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
        }

        .creation-card {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 30px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            background-color: rgba(255, 255, 255, 0.95);
            border: 8px solid white;
            position: relative;
            overflow: hidden;
        }

        .creation-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 15px;
            background: linear-gradient(90deg, #FF9A8B, #FF6A88, #FF99AC, #FCB69F, #FF9A8B);
            background-size: 500% 100%;
            animation: gradientBorder 4s ease infinite;
        }

        @keyframes gradientBorder {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        h1 {
            color: #3f72af;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .avatar-option {
            border: 4px solid transparent;
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            padding: 10px;
        }

        .avatar-option.selected {
            border-color: #3f72af;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(63, 114, 175, 0.3);
        }

        .avatar-option img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 10px;
        }

        .btn-confirm {
            background: linear-gradient(45deg, #4776E6, #8E54E9);
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 1.2rem;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-confirm:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(71, 118, 230, 0.3);
        }

        @media (max-width: 768px) {
            .creation-card {
                margin: 20px;
                padding: 20px;
            }
            .avatar-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="creation-card">
            <h1><i class="fas fa-user-astronaut me-2"></i> Create Your Character</h1>
            
            <form action="save_character.php" method="post">
                <div class="form-group mb-4">
                    <input type="text" class="form-control form-control-lg" name="username" placeholder="Choose your character name" required>
                </div>
                
                <h2 class="text-center mb-4"><i class="fas fa-robot me-2"></i> Select Your Avatar</h2>
                
                <div class="avatar-grid">
                    <!-- Avatar Options -->
                    <div class="avatar-option selected" data-avatar="boy1">
                        <img src="IMG/boy1.png" alt="Boy with red sunglasses">
                        <p class="text-center mt-2 mb-0">Alex</p>
                    </div>
                    <div class="avatar-option" data-avatar="girl1">
                        <img src="IMG/girl1.png" alt="Girl with glasses">
                        <p class="text-center mt-2 mb-0">Mia</p>
                    </div>
                    <div class="avatar-option" data-avatar="boy2">
                        <img src="IMG/boy2.png" alt="Boy in yellow shirt">
                        <p class="text-center mt-2 mb-0">Jay</p>
                    </div>
                    <div class="avatar-option" data-avatar="girl2">
                        <img src="IMG/girl2.png" alt="Girl with sunglasses">
                        <p class="text-center mt-2 mb-0">Zoe</p>
                    </div>
                    <div class="avatar-option" data-avatar="boy3">
                        <img src="IMG/boy3.png" alt="Boy in blue shirt">
                        <p class="text-center mt-2 mb-0">Sam</p>
                    </div>
                    <div class="avatar-option" data-avatar="girl3">
                        <img src="IMG/girl3.png" alt="Girl with blonde hair">
                        <p class="text-center mt-2 mb-0">Lily</p>
                    </div>
                </div>
                
                <input type="hidden" id="selectedAvatar" name="avatar" value="boy1">
                
                <div class="text-center mt-5">
                    <button type="submit" class="btn btn-confirm">
                        <i class="fas fa-check-circle me-2"></i> Confirm Character
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avatarOptions = document.querySelectorAll('.avatar-option');
            const selectedAvatarInput = document.getElementById('selectedAvatar');
            
            avatarOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    avatarOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Update the hidden input value
                    selectedAvatarInput.value = this.getAttribute('data-avatar');
                });
            });
        });
    </script>
</body>
</html>