<?php
session_start();
require_once 'db_config.php';
// ... (rest of your PHP code is unchanged) ...
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0 || (isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true)) {
    header("Location: login.php"); exit;
}
$user_id = $_SESSION['user_id'];
$hasPassword = false;
try {
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user && !empty($user['password_hash'])) { $hasPassword = true; }
} catch (PDOException $e) {
    header("Location: account_settings.php?error=Database error checking password status."); exit;
}
if (!$hasPassword) {
    header("Location: account_settings.php?error=Password login is not enabled for this account."); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Defuse It!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/3833be9c2c.js" crossorigin="anonymous"></script>

    <style>
        /* CRITICAL: Force viewport height and prevent scrolling */
        html { overflow: hidden !important; height: 100%; }
        body { overflow: hidden !important; height: 100%; }
        
        body.aesthetic-bg {
            background-image: url('assets/images/homescreen-bg.jpg');
            background-size: cover; background-position: center;
            background-attachment: fixed; background-color: rgba(0, 0, 0, 0.4); 
            background-blend-mode: multiply; min-height: 100vh; width: 100%;
        }
        
        /* Force the light, rounded aesthetic inputs */
        .auth-container-cartoon input[type="email"],
        .auth-container-cartoon input[type="password"],
        .auth-container-cartoon input[type="text"] {
            background: #ffffff !important; border: 2px solid #cccccc !important; 
            color: #333 !important; font-family: 'Poppins', sans-serif !important;
            border-radius: 8px !important; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            padding-right: 45px !important; 
            width: 100%; /* Ensure input takes full width */
            box-sizing: border-box; /* Include padding in width */
        }

        /* Force light theme labels and colors */
        .auth-container-cartoon label {
            color: #555 !important;
        }
        .auth-container-cartoon h1, .auth-container-cartoon p {
            color: #333 !important; font-family: 'Poppins', sans-serif !important; 
        }
        .auth-container-cartoon h1 {
            color: #e74c3c !important; font-family: 'Fredoka One', cursive !important;
        }

        /* --- (UPDATED) Password Mismatch & Eye Icon Layout Fix --- */
        .password-wrapper {
            width: 100%; 
        }
        .password-input-container {
            position: relative; /* (NEW) Positioning context for the eye */
            width: 100%;
            display: flex; /* Use flex to align input and button */
            align-items: center;
        }
        .toggle-password {
            position: absolute; /* Position absolutely within .password-input-container */
            right: 10px; 
            top: 50%;
            transform: translateY(-50%); /* This is now stable */
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            font-size: 1.1em;
            padding: 5px; 
            z-index: 10; 
        }
        .toggle-password:hover {
            color: #333;
        }
        .password-mismatch-notice {
            display: none; /* Hide by default; JS will show it */
            width: 100%; 
            text-align: left;
            margin-top: 6px;
            font-size: 0.9em;
            color: #e74c3c; 
            white-space: nowrap; 
        }
        /* --- End of Fix --- */
        
        /* Final Speaker UI Fix */
        #mute-button {
            position: fixed; bottom: 20px; right: 20px; 
            z-index: 1000;
            background-color: rgba(0, 0, 0, 0.8) !important; 
            border: 2px solid #ffffff !important; 
            color: #ffffff !important; 
            border-radius: 50%; width: 55px; height: 55px;
            font-size: 1.6em; cursor: pointer; display: flex;
            justify-content: center; align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4); 
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        #mute-button:hover {
             background-color: rgba(50, 50, 50, 0.9) !important; 
             color: #2ecc71 !important;
        }
    </style>
</head>
<body class="aesthetic-bg">
    
    <div class="auth-container-cartoon">
        <h1>Change Password</h1>
        <p>Enter your current password and choose a new one.</p>
        <?php
        if (isset($_GET['error'])) {
            $error = htmlspecialchars($_GET['error']);
            echo "<p class='error-notice'>$error</p>";
        }
        ?>
        <form action="handle_change_password.php" method="POST">
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                 <div class="password-wrapper">
                    <div class="password-input-container">
                        <input type="password" id="current_password" name="current_password" required>
                         <button type="button" class="toggle-password" aria-label="Show password as plain text...">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password (min. 8 characters)</label>
                <div class="password-wrapper">
                    <div class="password-input-container">
                        <input type="password" id="new_password" name="new_password" required>
                        <button type="button" class="toggle-password" aria-label="Show password as plain text...">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-mismatch-notice">Passwords do not match.</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <div class="password-input-container">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                         <button type="button" class="toggle-password" aria-label="Show password as plain text...">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-mismatch-notice">Passwords do not match.</div>
                </div>
            </div>
            <button type="submit" class="btn btn-rounded">Update Password</button>
        </form>
        <p class="auth-link">
            <a href="account_settings.php">Cancel</a>
        </p>
    </div>

    <button id="mute-button" title="Toggle Sound">
        <i class="fas fa-volume-mute"></i>
    </button>
    <audio id="audio-hover" src="assets/audio/ui-hover.wav" preload="auto"></audio>
    <audio id="audio-click" src="assets/audio/ui-click.wav" preload="auto"></audio>
    <audio id="audio-ambiance" src="assets/audio/ambiance-loop.wav" loop preload="auto"></audio>

    <script src="assets/js/main.js"></script>
</body>
</html>