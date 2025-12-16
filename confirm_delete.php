<?php
// confirm_delete.php
session_start();
date_default_timezone_set('UTC'); // Use UTC

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0 || (isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true)) {
    header("Location: login.php");
    exit;
}

$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'your email';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Account Deletion - Defuse It!</title>
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
        .auth-container-cartoon input[type="text"],
        .auth-container-cartoon .password-wrapper input {
            background: #ffffff !important; border: 2px solid #cccccc !important; 
            color: #333 !important; font-family: 'Poppins', sans-serif !important;
            border-radius: 8px !important; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1) !important;
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
        
        /* (NEW) Cartoon Danger Button */
        .btn-rounded.btn-danger-cartoon {
            background: #e74c3c;
            border-bottom: 4px solid #c0392b;
        }
        .btn-rounded.btn-danger-cartoon:hover {
            background: #c0392b;
            border-bottom: 4px solid #a03024;
        }

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
        <h1>Confirm Deletion</h1>
        <p style="color: #c0392b; font-weight: bold;">WARNING: This action is permanent and cannot be undone!</p>
        <p>A confirmation code has been sent to <?php echo $email; ?>. Please enter the code below to permanently delete your account.</p>
        <p style="font-size: 0.9em; color: #555;">The code expires in 15 minutes.</p>

        <?php
        if (isset($_GET['error'])) {
            $error = htmlspecialchars($_GET['error']);
            echo "<p class='error-notice'>$error</p>";
        }
        ?>

        <form action="handle_delete_account.php" method="POST">
            <div class="form-group">
                <label for="delete_code">Deletion Confirmation Code</label>
                <input type="text" id="delete_code" name="delete_code" required autofocus>
            </div>

            <button type="submit" class="btn btn-rounded btn-danger-cartoon">Confirm Permanent Deletion</button>
        </form>

        <p class="auth-link">
            <a href="account_settings.php">Cancel Deletion</a>
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