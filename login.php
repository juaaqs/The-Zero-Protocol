<?php
// login.php - "The Zero Protocol" LOGIN SCREEN
session_start();

// Check if we are being redirected back with an error.
$showIntro = !isset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Zero Protocol - Agent Clearance</title>
    
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

        /* --- Fullscreen Containers --- */
        .fullscreen-container {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            transition: opacity 0.5s ease-out;
        }
        
        /* --- INTRO SCREEN ("CLICK TO PROCEED") --- */
        #intro-screen {
            z-index: 10001; background-color: #000;
            padding: 20px; box-sizing: border-box;
            display: <?php echo $showIntro ? 'flex' : 'none'; ?>; 
            opacity: <?php echo $showIntro ? '1' : '0'; ?>;
        }
        
        .intro-text {
            font-family: 'Special Elite', monospace; font-size: 2.2em;
            color: #b71c1c; text-align: center; text-transform: uppercase;
        }

        /* --- Sci-Fi Button Style (Blue) --- */
        .btn-proceed {
            display: block;
            width: 600px;
            padding: 15px 12px;
            margin-top: 30px;
            font-family: 'Special Elite', monospace; font-size: 1.5em;
            text-align: center; text-decoration: none;
            text-transform: uppercase; 
            background-color: rgba(0, 170, 255, 0.1);
            border: 1px solid #00aaff;
            color: #00aaff;
            text-shadow: 0 0 10px rgba(0, 170, 255, 0.7);
            cursor: pointer; box-sizing: border-box;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-proceed:hover { 
            background-color: rgba(0, 170, 255, 0.3);
            color: #fff;
            box-shadow: 0 0 15px #00aaff;
        }
        
        /* --- MAIN CONTENT (Starts hidden) --- */
        #main-content {
            display: <?php echo $showIntro ? 'none' : 'flex'; ?>;
            opacity: <?php echo $showIntro ? '0' : '1'; ?>;
        }

        /* --- VIDEO & GRAIN --- */
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

        /* --- LOGIN CONTAINER --- */
        .dossier-wrapper {
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            flex-direction: column; /* Stack title and box */
            position: fixed; top: 0; left: 0; z-index: 4;
        }
        .hub-title {
            font-family: 'Special Elite', monospace;
            font-size: 5.5em; 
            color: #b71c1c;   
            text-shadow: 0 0 15px #b71c1c, 0 0 5px #ff0000; /* Red glow */
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
            z-index: 5;
            text-align: center;
        }
        .login-container {
            position: relative; z-index: 5; width: 450px;
            padding: 30px 40px; 
            background: rgba(0,0,0,0.2);
            box-shadow: 0 5px 25px rgba(0,0,0,0.4);
        }
        
        .login-notice {
            font-family: 'Merriweather', serif; font-size: 0.9em;
            color: #f5eeda; /* Light text */
            text-align: center; margin-bottom: 20px; padding: 10px;
            background-color: rgba(0, 170, 255, 0.1); /* Blue transparent */
            border: 1px dashed rgba(0, 170, 255, 0.5); /* Blue dashed */
        }
        
        /* --- Sci-Fi Form --- */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            font-family: 'Special Elite', monospace; font-size: 1.1em;
            color: #f5eeda; /* Light text */
            display: block; margin-bottom: 8px;
            text-shadow: 0 0 5px rgba(245, 238, 218, 0.5);
        }
        .form-group input {
            font-family: 'Merriweather', serif; font-size: 1.1em;
            width: 100%; padding: 10px; box-sizing: border-box; 

            background-color: rgba(0,0,0,0.3); /* Dark bg */
            color: #f5eeda; /* Light text */
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-group input:focus {
            outline: none; 
            border-color: #00aaff;
            box-shadow: 0 0 10px rgba(0, 170, 255, 0.7);
        }
        
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
            color: #00aaff; /* Blue eye */
            font-size: 1.1em;
            padding: 5px;
        }
        .toggle-password:hover { color: #fff; }
        
        /* --- Sci-Fi Buttons --- */
        .button-group {
            display: flex; justify-content: space-between; gap: 10px; margin-bottom: 10px;
        }
        .btn {
            display: block; flex-grow: 1; width: 50%; padding: 12px 5px;
            font-family: 'Special Elite', monospace; font-size: 1.2em; text-align: center;
            text-decoration: none; text-transform: uppercase;
            background-color: rgba(0, 170, 255, 0.1);
            border: 1px solid #00aaff;
            color: #00aaff;
            text-shadow: 0 0 10px rgba(0, 170, 255, 0.7);
            cursor: pointer; box-sizing: border-box;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .btn:hover { 
            background-color: rgba(0, 170, 255, 0.3);
            color: #fff;
            box-shadow: 0 0 15px #00aaff;
        }
        
        /* Ghost Button */
        .btn-secondary { 
            background-color: transparent; 
            border: 1px solid rgba(0, 170, 255, 0.5);
        }
        .btn-secondary:hover { 
            background-color: rgba(0, 170, 255, 0.1);
            color: #00aaff;
        }
        
        /* Google Button (Red) */
        .btn-google {
            display: block; width: 100%; 
            background-color: rgba(183, 28, 28, 0.1);
            border: 1px solid #b71c1c;
            color: #b71c1c;
            text-shadow: 0 0 10px rgba(183, 28, 28, 0.7);
            font-size: 1.2em;
        }
        .btn-google:hover {
            background-color: rgba(183, 28, 28, 0.3);
            color: #f5eeda;
            box-shadow: 0 0 15px #b71c1c;
        }

        .separator {
            font-family: 'Special Elite', monospace; text-align: center; 
            color: rgba(255,255,255,0.4); 
            margin: 15px 0;
        }
        
        .forgot-link-wrapper {
            text-align: right;
            margin-bottom: 20px;
        }
        .forgot-link {
            font-family: 'Merriweather', serif;
            font-size: 0.9em;
            color: #00aaff; /* Blue link */
            text-decoration: underline;
        }
        .forgot-link:hover { color: #fff; }
        
        .dossier-footer { text-align: center; margin-top: 20px; }
        .dossier-footer a {
            font-family: 'Merriweather', serif; color: #00aaff; text-decoration: underline;
        }
        .dossier-footer a:hover { color: #fff; }
        
        /* Sci-Fi Messages */
        .message {
            font-family: 'Special Elite', monospace; font-size: 1.1em;
            text-align: center; margin-bottom: 15px; border: 1px dashed; padding: 10px;
        }
        .message.error {
            color: #b71c1c; 
            border-color: #b71c1c; 
            background-color: rgba(183, 28, 28, 0.1);
        }
        .message.success {
            color: #27ae60; /* Green */
            border-color: #27ae60; 
            background-color: rgba(39, 174, 96, 0.1);
        }
        
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

    <div id="intro-screen" class="fullscreen-container" <?php if (!$showIntro) echo 'style="display: none;"'; ?>>
        <div style="text-align: center;">
            <div class="intro-text">AGENT CLEARANCE REQUIRED</div>
            <button class="btn-proceed" id="intro-continue-btn">[ Click to Verify Identity ]</button>
        </div>
    </div>

    <div id="main-content" class="fullscreen-container" <?php if (!$showIntro) echo 'style="display: flex; opacity: 1;"'; ?>>

        <div class="video-background">
            <video autoplay muted loop playsinline id="bg-video">
                <source src="assets/video/bg-video.mp4" type="video/mp4">
            </video>
        </div>

        <div class="film-grain-overlay"></div>

        <div class="dossier-wrapper">
            
            <h1 class="hub-title">Agent Clearance</h1>
            
            <div class="login-container">
                                
                <p class="login-notice">
                    <strong>Notice:</strong> Agent account required to save mission progress and track leaderboard scores.
                </p>
                
                <?php
                    // This now shows all messages, including "Account already exists"
                    if (isset($_SESSION['message'])) {
                        $message_class = ($_SESSION['message']['type'] === 'success') ? 'success' : 'error';
                        $message_text = $_SESSION['message']['text'];
                        echo '<div class="message ' . $message_class . '">' . htmlspecialchars($message_text) . '</div>';
                        unset($_SESSION['message']); // Clear the message
                    }
                ?>
                
                <form action="auth_controller.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Passcode:</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="forgot-link-wrapper">
                        <a href="forgot_password.php" class="forgot-link">Forgot Passcode?</a>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn">Access Terminal</button>
                        <a href="handle_guest.php" class="btn btn-secondary">Anonymous Entry</a>
                    </div>
                </form>
                
                <div class="separator">- OR -</div>

                <a href="auth_controller.php?action=google_login&from=login" class="btn btn-google">
                    <i class="fab fa-google"></i> Login with Google
                </a>
                <div class="dossier-footer">
                    <a href="signup.php">Register New Operative</a> | <a href="index.php">Abort</a>
                </div>
            </div>
        </div>

        <button id="mute-button" title="<?php echo $showIntro ? 'Mute' : 'Unmute'; ?>">
            <i id="mute-icon" class="fas <?php echo $showIntro ? 'fa-volume-up' : 'fa-volume-mute'; ?>"></i>
        </button>
        
        <audio id="audio-hover" src="assets/audio/ui-hover.wav" preload="auto"></audio>
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
            
            let isMuted = <?php echo $showIntro ? 'false' : 'true'; ?>;
            
            // Get the volume level from the cookie, default to 30
            const savedVolume = document.cookie.split('; ').find(row => row.startsWith('game_volume='))?.split('=')[1] ?? 30;
            escapeAudio.volume = savedVolume / 100;

            hoverAudio.volume = 0.8;
            clickAudio.volume = 0.5; 
            
            if (introButton && introScreen.style.display !== 'none') {
                introButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    playEscape();
                    introScreen.style.opacity = '0';
                    setTimeout(() => { introScreen.style.display = 'none'; }, 500);
                    mainContent.style.display = 'flex';
                    setTimeout(() => { mainContent.style.opacity = '1'; }, 10);
                });
            } else {
                // If intro is hidden (due to error redirect), play audio on next click
                mainContent.addEventListener('click', playEscape, { once: true });
            }

            function playEscape() {
                if (escapeAudio.paused) {
                    escapeAudio.play().catch(e => console.log("Audio play failed."));
                }
                if (!isMuted) {
                    escapeAudio.muted = false;
                }
            }

            if (muteButton) {
                muteButton.addEventListener('click', () => {
                    isMuted = !isMuted; 
                    escapeAudio.muted = !escapeAudio.muted; 
                    muteIcon.className = isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
                    muteButton.title = isMuted ? 'Unmute' : 'Mute';
                    playEscape();
                });
            }
            
            const allButtons = document.querySelectorAll('.btn, .btn-secondary, .btn-google, .btn-proceed, .dossier-footer a, .forgot-link, .toggle-password');
            allButtons.forEach(button => {
                button.addEventListener('mouseenter', () => {
                    if (!isMuted) hoverAudio.play();
                });
            });

            document.body.addEventListener('click', (e) => {
                // Stop click sound if clicking a button (buttons play their own)
                if (e.target.closest('button') || e.target.closest('a')) {
                    return;
                }
                if (!isMuted) clickAudio.cloneNode(true).play();
            });

            // --- Password Visibility Toggle ---
            const toggleButtons = document.querySelectorAll('.toggle-password');
            toggleButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.stopPropagation(); 
                    if (!isMuted) clickAudio.cloneNode(true).play();
                    
                    const input = button.closest('.password-wrapper').querySelector('input');
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

        });
    </script>

</body>
</html>