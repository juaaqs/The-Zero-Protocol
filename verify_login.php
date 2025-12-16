<?php
// verify_login.php - "The Zero Protocol" 2FA SCREEN
session_start();

// If the user isn't in the middle of a 2FA check, send them away.
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Zero Protocol - Security Checkpoint</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Special+Elite&display=swap" rel="stylesheet">
    
    <script src="https://kit.fontawesome.com/3833be9c2c.js" crossorigin="anonymous"></script>
    
    <style>
        /* --- Base & Body --- */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Merriweather', serif;
            overflow: hidden;
            background-color: #000;
        }
        
        /* ... (all your other CSS is correct and goes here) ... */

        /* --- Fullscreen Containers --- */
        .fullscreen-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease-out;
        }
        
        /* --- INTRO SCREEN ("CLICK TO PROCEED") --- */
        #intro-screen {
            z-index: 10001;
            background-color: #000;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .intro-text {
            font-family: 'Special Elite', monospace;
            font-size: 2.2em;
            color: #b71c1c;
            text-align: center;
            text-transform: uppercase;
        }

        .btn-proceed {
            font-family: 'Special Elite', monospace;
            font-size: 1.8em;
            color: #f5eeda;
            background: #333;
            border: 2px solid #f5eeda;
            padding: 15px 30px;
            cursor: pointer;
            margin-top: 30px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .btn-proceed:hover {
            background-color: #f5eeda;
            color: #333;
        }
        
        /* --- MAIN CONTENT (Starts hidden) --- */
        #main-content {
            opacity: 0;
            display: none;
        }

        /* --- VIDEO & GRAIN --- */
        .video-background {
            position: fixed;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translateX(-50%) translateY(-50%);
            z-index: 1;
            pointer-events: none;
        }
        #bg-video {
            width: 100vw; height: 56.25vw; min-height: 100vh;
            min-width: 177.77vh; position: absolute; top: 50%;
            left: 50%; transform: translate(-50%, -50%);
        }
        .video-background::after {
            content: ''; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.65);
            z-index: 2;
        }
        .film-grain-overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background-image: url('assets/images/noise-overlay.png');
            animation: grain 0.4s steps(1) infinite;
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

        /* --- VERIFY DOSSIER --- */
        .dossier-wrapper {
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            position: fixed; top: 0; left: 0; z-index: 4;
        }
        .dossier-container {
            position: relative; z-index: 5; width: 420px;
            padding: 30px 40px; background-color: #f5eeda; 
            border: 1px solid #c9b79c;
            box-shadow: 10px 10px 25px rgba(0, 0, 0, 0.5);
        }

        .dossier-title {
            font-family: 'Special Elite', monospace; font-size: 2.5em;
            color: #b71c1c; /* Red title for security */
            text-align: center;
            margin: 0 0 15px 0; text-transform: uppercase;
        }
        
        .verify-notice {
            font-family: 'Merriweather', serif;
            font-size: 1em;
            color: #333;
            text-align: center;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        /* --- NEW PIN ENTRY BOXES --- */
        .pin-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }
        .pin-input {
            width: 50px;
            height: 60px;
            font-family: 'Special Elite', monospace;
            font-size: 2em;
            text-align: center;
            border: none;
            border-bottom: 3px solid #aaa;
            background-color: rgba(210, 180, 140, 0.2);
            transition: border-color 0.3s;
        }
        .pin-input:focus {
            outline: none;
            border-bottom-color: #b71c1c;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            font-family: 'Special Elite', monospace; font-size: 1.3em;
            text-align: center; text-decoration: none;
            text-transform: uppercase; border: 2px solid #333;
            background-color: #333; color: #f5eeda;
            cursor: pointer; box-sizing: border-box;
            box-shadow: 0 5px 0 #1a1a1a;
            position: relative; top: 0;
            transition: background-color 0.2s ease, top 0.1s ease, box-shadow 0.1s ease;
        }
        .btn:hover { background-color: #000; border-color: #000; }
        .btn:active { top: 3px; box-shadow: 0 2px 0 #1a1a1a; }
        
        .dossier-footer { text-align: center; margin-top: 20px; }
        .dossier-footer a {
            font-family: 'Merriweather', serif; color: #5d4037;
            text-decoration: underline;
        }
        .dossier-footer a:hover { color: #b71c1c; }
        
        .message {
            font-family: 'Special Elite', monospace; font-size: 1.1em;
            text-align: center; margin-bottom: 15px; border: 1px dashed; padding: 10px;
            background-color: rgba(0,0,0, 0.05);
        }
        .message.error {
            color: #b71c1c; border-color: #b71c1c; background-color: rgba(183, 28, 28, 0.05);
        }
        .message.success {
            color: #556B2F; border-color: #556B2F; background-color: rgba(85, 107, 47, 0.05);
        }
        
        #mute-button {
            position: fixed; bottom: 20px; right: 20px; z-index: 1000;
            background-color: #333; border: 2px solid #f5eeda;
            color: #f5eeda; border-radius: 50%; width: 55px; height: 55px;
            font-size: 1.6em; cursor: pointer; display: flex;
            justify-content: center; align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        #mute-button:hover { background-color: #000; transform: scale(1.1); }
    </style>
</head>
<body>

    <div id="intro-screen" class="fullscreen-container">
        <div style="text-align: center;">
            <div class="intro-text">SECURITY CHECKPOINT</div>
            <button class="btn-proceed" id="intro-continue-btn">[ Verify Access ]</button>
        </div>
    </div>

    <div id="main-content" class="fullscreen-container">

        <div class="video-background">
            <video autoplay muted loop playsinline id="bg-video">
                <source src="assets/video/bg-video.mp4" type="video/mp4">
            </video>
        </div>

        <div class="film-grain-overlay"></div>

        <div class="dossier-wrapper">
            <div class="dossier-container">
                
                <h1 class="dossier-title">Security Checkpoint</h1>
                
                <p class="verify-notice">
                    A 6-digit passcode has been sent to your secure email. Enter it below to proceed.
                </p>
                
                <?php
                    // This now checks for the correct $_SESSION['message']
                    if (isset($_SESSION['message'])) {
                        $message_class = ($_SESSION['message']['type'] === 'success') ? 'success' : 'error';
                        $message_text = $_SESSION['message']['text'];
                        echo '<div class="message ' . $message_class . '">' . htmlspecialchars($message_text) . '</div>';
                        unset($_SESSION['message']); // Clear the message
                    }
                ?>
                
                <form action="auth_controller.php" method="POST">
                    <input type="hidden" name="action" value="verify_2fa">
                    
                    <div class="pin-group" id="pin-container">
                        <input type="text" class="pin-input" name="pin-1" maxlength="1" required>
                        <input type="text" class="pin-input" name="pin-2" maxlength="1" required>
                        <input type="text" class="pin-input" name="pin-3" maxlength="1" required>
                        <input type="text" class="pin-input" name="pin-4" maxlength="1" required>
                        <input type="text" class="pin-input" name="pin-5" maxlength="1" required>
                        <input type="text" class="pin-input" name="pin-6" maxlength="1" required>
                    </div>
                    
                    <button type="submit" class="btn">Verify Passcode</button>
                </form>
                
                <div class="dossier-footer">
                    <a href="auth_controller.php?action=resend_code">Resend Code</a> | <a href="auth_controller.php?action=logout">Abort</a>
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
            
            const introScreen = document.getElementById('intro-screen');
            const introButton = document.getElementById('intro-continue-btn');
            const mainContent = document.getElementById('main-content');
            
            const escapeAudio = document.getElementById('audio-escape');
            const hoverAudio = document.getElementById('audio-hover');
            const clickAudio = document.getElementById('audio-click');
            const muteButton = document.getElementById('mute-button');
            const muteIcon = document.getElementById('mute-icon');
            
            let isMuted = false;

            escapeAudio.volume = 0.3;
            hoverAudio.volume = 0.8;
            clickAudio.volume = 0.5; 
            
            if (introButton) {
                introButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    playEscape();
                    introScreen.style.opacity = '0';
                    setTimeout(() => { introScreen.style.display = 'none'; }, 500);
                    mainContent.style.display = 'block';
                    setTimeout(() => { 
                        mainContent.style.opacity = '1';
                        // Focus the first pin box
                        document.querySelector('.pin-input').focus();
                    }, 10);
                });
            }

            function playEscape() {
                if (escapeAudio.paused) {
                    escapeAudio.play().catch(e => console.log("Audio play failed."));
                }
                escapeAudio.muted = isMuted;
            }

            if (muteButton) {
                muteButton.addEventListener('click', () => {
                    isMuted = !isMuted; 
                    escapeAudio.muted = isMuted; 
                    muteIcon.className = isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
                    muteButton.title = isMuted ? 'Unmute' : 'Mute';
                });
            }
            
            // --- UI Sound Listeners ---
            const allButtons = document.querySelectorAll('.btn, .btn-proceed, .dossier-footer a');
            allButtons.forEach(button => {
                button.addEventListener('mouseenter', () => {
                    hoverAudio.play();
                });
            });

            document.body.addEventListener('click', () => {
                clickAudio.cloneNode(true).play();
            });

            // --- NEW PIN ENTRY SCRIPT ---
            const pinContainer = document.getElementById('pin-container');
            if (pinContainer) {
                const inputs = [...pinContainer.children];
                inputs.forEach((input, index) => {
                    input.addEventListener('input', (e) => {
                        // On input, if there's a next box, focus it
                        if (index < inputs.length - 1 && input.value) {
                            inputs[index + 1].focus();
                        }
                    });
                    
                    input.addEventListener('keydown', (e) => {
                        // On backspace, if the box is empty, focus the previous one
                        if (e.key === 'Backspace' && !input.value && index > 0) {
                            inputs[index - 1].focus();
                        }
                    });
                });
            }
        });
    </script>

</body>
</html>