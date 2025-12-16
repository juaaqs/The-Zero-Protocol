<?php
// reset_password.php - "The Zero Protocol" SECURE PASSWORD RESET
session_start();
require 'db_config.php'; 

$token = $_GET['token'] ?? '';
$is_token_valid = false;
$reset_status_message = "ACCESS DENIED: Invalid or missing secure token.";

if (!empty($token)) {
    try {
        // 1. Check for the token in the verification_codes table
        // We also check if the token has expired
        $stmt = $pdo->prepare("SELECT user_id, expires_at FROM verification_codes WHERE code = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset_record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reset_record) {
            // Token is valid and not expired!
            $is_token_valid = true;
            $_SESSION['reset_user_id'] = $reset_record['user_id'];
            $_SESSION['reset_token'] = $token;
            $reset_status_message = "ACCESS GRANTED. Enter your new passcode below.";
        } else {
            // If fetch fails, the token is either invalid or expired
            $reset_status_message = "SECURE TOKEN EXPIRED. Please request a new link.";
        }

    } catch (PDOException $e) {
        error_log("Reset Token DB Error: " . $e->getMessage());
        $reset_status_message = "SYSTEM ERROR. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Zero Protocol - Passcode Reset</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Special+Elite&display=swap" rel="stylesheet">
    
    <script src="https://kit.fontawesome.com/3833be9c2c.js" crossorigin="anonymous"></script>
    
    <style>
        /* --- Base & Body --- */
        html, body {
            height: 100%; margin: 0; padding: 0;
            font-family: 'Merriweather', serif; overflow: hidden;
            background-color: #000;
        }
        .fullscreen-container {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            transition: opacity 0.5s ease-out;
        }
        #main-content { opacity: 1; }
        .video-background {
            position: fixed; top: 50%; left: 50%; min-width: 100%; min-height: 100%;
            width: auto; height: auto; transform: translateX(-50%) translateY(-50%);
            z-index: 1; pointer-events: none;
        }
        #bg-video {
            width: 100vw; height: 56.25vw; min-height: 100vh; min-width: 177.77vh;
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        }
        .video-background::after {
            content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.65); z-index: 2;
        }
        .film-grain-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('assets/images/noise-overlay.png'); animation: grain 0.4s steps(1) infinite;
            z-index: 3; pointer-events: none; opacity: 0.15;
        }
        @keyframes grain {
            0% { transform: translate(0, 0); } 10% { transform: translate(-1px, -1px); }
            20% { transform: translate(1px, 1px); } 30% { transform: translate(-2px, 1px); }
            40% { transform: translate(2px, -1px); } 50% { transform: translate(-1px, 2px); }
            60% { transform: translate(1px, -2px); } 70% { transform: translate(-2px, -2px); }
            80% { transform: translate(2px, 2px); } 90% { transform: translate(-1px, 1px); }
            100% { transform: translate(1px, -1px); }
        }

        /* --- PASSWORD RESET DOSSIER --- */
        .dossier-wrapper {
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            position: fixed; top: 0; left: 0; z-index: 4;
        }
        .dossier-container {
            position: relative; z-index: 5; width: 420px;
            padding: 30px 40px; background-color: #f5eeda; 
            border: 1px solid #c9b79c; box-shadow: 10px 10px 25px rgba(0, 0, 0, 0.5);
        }

        /* --- Status Message Style --- */
        .status-header {
            font-family: 'Special Elite', monospace;
            font-size: 1.5em;
            text-align: center;
            margin-bottom: 25px;
            padding: 10px;
            border: 2px dashed;
        }
        .status-header.granted {
            color: #556B2F; /* Olive Green */
            border-color: #556B2F;
        }
        .status-header.denied {
            color: #b71c1c; /* Red */
            border-color: #b71c1c;
        }

        /* --- Form/Input Styles --- */
        .dossier-title {
            font-family: 'Special Elite', monospace; font-size: 2.5em;
            color: #2F2F2F; text-align: center; margin: 0 0 15px 0; text-transform: uppercase;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            font-family: 'Special Elite', monospace; font-size: 1.1em;
            color: #333; display: block; margin-bottom: 8px;
        }
        .form-group input {
            font-family: 'Merriweather', serif; font-size: 1.1em;
            width: 100%; padding: 10px; box-sizing: border-box; border: none; border-bottom: 2px solid #aaa;
            background-color: rgba(210, 180, 140, 0.2); transition: border-color 0.3s, background-color 0.3s;
        }
        .form-group input:focus {
            outline: none; border-bottom-color: #b71c1c; background-color: rgba(210, 180, 140, 0.4);
        }
        
        /* --- NEW: Password Visibility Styles --- */
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .password-wrapper input {
            width: 100%;
            padding-right: 40px; /* Space for the eye icon */
        }
        .toggle-password {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #777;
            font-size: 1.1em;
            padding: 5px;
        }
        .toggle-password:hover { color: #000; }
        
        /* --- NEW: Password Match Message --- */
        .password-match-status {
            font-family: 'Special Elite', monospace;
            font-size: 0.9em;
            margin-top: 5px;
            text-align: left;
            display: none; /* Hidden by default */
        }
        .password-match-status.mismatch { color: #b71c1c; }
        .password-match-status.match { color: #556B2F; }

        .btn {
            display: block; width: 100%; padding: 12px; margin-bottom: 10px;
            font-family: 'Special Elite', monospace; font-size: 1.3em;
            text-align: center; text-decoration: none; text-transform: uppercase; border: 2px solid #333;
            background-color: #333; color: #f5eeda; cursor: pointer; box-sizing: border-box;
            box-shadow: 0 5px 0 #1a1a1a; position: relative; top: 0;
            transition: background-color 0.2s ease, top 0.1s ease, box-shadow 0.1s ease;
        }
        .btn:hover { background-color: #000; border-color: #000; }
        .btn:active { top: 3px; box-shadow: 0 2px 0 #1a1a1a; }
        
        .dossier-footer { text-align: center; margin-top: 20px; }
        .dossier-footer a {
            font-family: 'Merriweather', serif; color: #5d4037; text-decoration: underline;
        }
        .dossier-footer a:hover { color: #b71c1c; }
        
        #mute-button {
            position: fixed; bottom: 20px; right: 20px; z-index: 1000; background-color: #333;
            border: 2px solid #f5eeda; color: #f5eeda; border-radius: 50%; width: 55px; height: 55px;
            font-size: 1.6em; cursor: pointer; display: flex; justify-content: center; align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5); transition: background-color 0.2s ease, transform 0.2s ease;
        }
        #mute-button:hover { background-color: #000; transform: scale(1.1); }
    </style>
</head>
<body>

    <div id="main-content" class="fullscreen-container">

        <div class="video-background">
            <video autoplay muted loop playsinline id="bg-video">
                <source src="assets/video/bg-video.mp4" type="video/mp4">
            </video>
        </div>

        <div class="film-grain-overlay"></div>

        <div class="dossier-wrapper">
            <div class="dossier-container">
                
                <h1 class="dossier-title">Passcode Reset Terminal</h1>

                <div class="status-header <?php echo $is_token_valid ? 'granted' : 'denied'; ?>">
                    <?php echo htmlspecialchars($reset_status_message); ?>
                </div>
                
                <?php if ($is_token_valid): ?>
                    
                    <form action="handle_reset_password.php" method="POST">
                        <div class="form-group">
                            <label for="new_password">New Passcode:</label>
                            <div class="password-wrapper">
                                <input type="password" id="new_password" name="new_password" required>
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Passcode:</label>
                            <div class="password-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="password-match-status" id="password-match-status"></div>
                        </div>
                        
                        <button type="submit" class="btn">Confirm New Passcode</button>
                    </form>
                    
                <?php else: ?>
                    
                    <p style="text-align: center; font-size: 1em; color: #555;">
                        If you need to try again, please return to the login screen and request a new link.
                    </p>
                    
                <?php endif; ?>
                
                <div class="dossier-footer">
                    <a href="login.php">Return to Agent Clearance</a>
                </div>

            </div>
        </div>

        <button id="mute-button" title="Mute">
            <i id="mute-icon" class="fas fa-volume-up"></i>
        </button>
        
        <audio id="audio-hover" src="assets/audio/hover.mp3" preload="auto"></audio>
        <audio id="audio-click"src="assets/audio/ui-click.wav" preload="auto"></audio>
        <audio id="audio-escape" src="assets/audio/escape.mp3" loop preload="auto"></audio>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            
            const escapeAudio = document.getElementById('audio-escape');
            const hoverAudio = document.getElementById('audio-hover');
            const clickAudio = document.getElementById('audio-click');
            const muteButton = document.getElementById('mute-button');
            
            let isMuted = false;

            escapeAudio.volume = 0.3;
            hoverAudio.volume = 0.8;
            clickAudio.volume = 0.5; 
            
            escapeAudio.play().catch(e => console.log("Audio play failed."));

            if (muteButton) {
                muteButton.addEventListener('click', () => {
                    isMuted = !escapeAudio.muted;
                    escapeAudio.muted = isMuted; 
                    muteButton.title = isMuted ? 'Unmute' : 'Mute';
                    muteButton.querySelector('#mute-icon').className = isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
                });
            }
            
            const allButtons = document.querySelectorAll('.btn, .dossier-footer a');
            allButtons.forEach(button => {
                button.addEventListener('mouseenter', () => {
                    if (!isMuted) hoverAudio.play();
                });
            });

            document.body.addEventListener('click', () => {
                if (!isMuted) clickAudio.cloneNode(true).play();
            });

            // --- Password Visibility Toggle ---
            const toggleButtons = document.querySelectorAll('.toggle-password');
            toggleButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const input = button.previousElementSibling;
                    const icon = button.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // --- Password Match Check ---
            const passwordInput = document.getElementById('new_password');
            const confirmInput = document.getElementById('confirm_password');
            const matchStatus = document.getElementById('password-match-status');

            // Only run this script if the form is actually visible
            if (passwordInput) { 
                function checkPasswordMatch() {
                    const pass = passwordInput.value;
                    const confirmPass = confirmInput.value;
                    
                    if (confirmPass.length > 0) {
                        if (pass === confirmPass) {
                            matchStatus.textContent = 'Passcodes match.';
                            matchStatus.className = 'password-match-status match';
                            matchStatus.style.display = 'block';
                        } else {
                            matchStatus.textContent = 'Passcodes do not match.';
                            matchStatus.className = 'password-match-status mismatch';
                            matchStatus.style.display = 'block';
                        }
                    } else {
                        matchStatus.style.display = 'none';
                    }
                }
                passwordInput.addEventListener('keyup', checkPasswordMatch);
                confirmInput.addEventListener('keyup', checkPasswordMatch);
            }
        });
    </script>

</body>
</html>