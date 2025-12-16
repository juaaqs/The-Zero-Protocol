<?php
// menu.php - "The Zero Protocol" AGENT HUB
session_start();

// If the user isn't logged in, send them to login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user details from the session
$username = $_SESSION['username'] ?? 'Agent'; 
$avatar_url = $_SESSION['avatar_url'] ?? 'assets/images/avatar-default.png';
$isGuest = $_SESSION['is_guest'] ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Zero Protocol - Mission Control</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Special+Elite&display=swap" rel="stylesheet">
    
    <script src="https://kit.fontawesome.com/3833be9c2c.js" crossorigin="anonymous"></script>
    
    <style>
        /* --- Base & Body --- */
        html, body {
            height: 100%; margin: 0; padding: 0;
            font-family: 'Merriweather', serif;
            overflow: hidden; background-color: #000;
        }
        .fullscreen-container {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            transition: opacity 0.5s ease-out;
        }
        #intro-screen {
            z-index: 10001; background-color: #000;
            padding: 20px; box-sizing: border-box;
        }
        .intro-text {
            font-family: 'Special Elite', monospace; font-size: 2.2em;
            color: #b71c1c; text-align: center; text-transform: uppercase;
        }
        .btn-proceed {
            font-family: 'Special Elite', monospace; font-size: 1.8em;
            color: #f5eeda; background: #333; border: 2px solid #f5eeda;
            padding: 15px 30px; cursor: pointer; margin-top: 30px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .btn-proceed:hover { background-color: #f5eeda; color: #333; }
        #main-content {
            opacity: 0; display: none;
        }
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
            content: ''; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.65); z-index: 2;
        }
        .film-grain-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('assets/images/noise-overlay.png');
            animation: grain 0.4s steps(1) infinite; z-index: 3;
            pointer-events: none; opacity: 0.15;
        }
        @keyframes grain {
            0% { transform: translate(0, 0); } 10% { transform: translate(-1px, -1px); }
            20% { transform: translate(1px, 1px); } 30% { transform: translate(-2px, 1px); }
            40% { transform: translate(2px, -1px); } 50% { transform: translate(-1px, 2px); }
            60% { transform: translate(1px, -2px); } 70% { transform: translate(-2px, -2px); }
            80% { transform: translate(2px, 2px); } 90% { transform: translate(-1px, 1px); }
            100% { transform: translate(1px, -1px); }
        }
        
        /* --- Wrapper stacks title AND box --- */
        .dossier-wrapper {
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            position: fixed; top: 0; left: 0; z-index: 4;
            flex-direction: column; /* Stack vertically */
        }
        
        /* --- Big Red Elegant Title --- */
        .hub-title {
            font-family: 'Special Elite', monospace;
            font-size: 5.5em; /* Big */
            color: #b71c1c;   /* Red */
            text-shadow: 0 0 15px #b71c1c, 0 0 5px #ff0000; /* Red glow */
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
            z-index: 5;
        }

        /* --- REMOVED: dossier-container (white box) --- */
        /* This container just holds the buttons now */
        .button-container {
             width: 450px;
             text-align: center;
             padding: 20px;
             /* Transparent background with a subtle border */
             background: rgba(0,0,0,0.2);
             box-shadow: 0 5px 25px rgba(0,0,0,0.4);
        }

        /* --- Agent ID Card (Sci-fi style) --- */
        .agent-id-card {
            display: flex;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(183, 28, 28, 0.5); /* Red separator */
            position: relative; 
        }
        .agent-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #b71c1c; /* Red border */
            object-fit: cover;
            margin-right: 20px;
            box-shadow: 0 0 15px rgba(183, 28, 28, 0.5); /* Red glow */
        }
        .agent-info {
            text-align: left;
        }
        .agent-info h1 {
            font-family: 'Special Elite', monospace;
            font-size: 1.8em;
            color: #f5eeda; /* Light text */
            text-shadow: 0 0 5px rgba(245, 238, 218, 0.5);
            margin: 0 0 5px 0;
            line-height: 1.2;
        }
        .agent-info p {
            font-family: 'Merriweather', serif;
            font-size: 1.1em;
            color: #b71c1c; /* Red text */
            margin: 0;
            font-weight: bold;
        }

        #credits-button {
            position: absolute;
            top: 10px;
            right: 0px; /* Aligned to the edge */
            font-size: 1.6em;
            color: #f5eeda; /* Light text */
            cursor: pointer;
            transition: color 0.2s ease, transform 0.2s ease;
        }
        #credits-button:hover {
            color: #b71c1c;
            transform: scale(1.1);
        }

        /* --- Sci-Fi Button Style --- */
        .btn-menu {
            display: block;
            width: 100%;
            padding: 15px 12px;
            margin-bottom: 15px;
            font-family: 'Special Elite', monospace; font-size: 1.5em;
            text-align: center; text-decoration: none;
            text-transform: uppercase; 
            
            /* New Sci-Fi Style */
            background-color: rgba(0, 170, 255, 0.1); /* Faint blue bg */
            border: 1px solid #ffffffff; /* Blue border */
            color: #222222ff; /* Blue text */
            text-shadow: 0 0 10px rgba(0, 170, 255, 0.7); /* Blue glow */
            
            cursor: pointer; box-sizing: border-box;
            position: relative; top: 0;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-menu:hover { 
            background-color: rgba(0, 170, 255, 0.3);
            color: #fff;
            box-shadow: 0 0 15px #00aaff;
        }
        .btn-menu:active { 
            top: 1px;
            background-color: rgba(255, 255, 255, 0.5);
        }
        
        /* Logout button (Red/Danger style) */
        .btn-logout {
            background-color: rgba(183, 28, 28, 0.1);
            border: 1px solid #b71c1c;
            color: #b71c1c;
            text-shadow: 0 0 10px rgba(183, 28, 28, 0.7);
            font-size: 1.3em;
            margin-top: 10px;
        }
        .btn-logout:hover {
            background-color: rgba(183, 28, 28, 0.3);
            color: #f5eeda;
            box-shadow: 0 0 15px #b71c1c;
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
        
        /* --- Sci-Fi Credits Modal --- */
        #credits-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            z-index: 20000;
            display: none; 
            justify-content: center;
            align-items: center;
        }
        .credits-content {
            /* Dark Sci-Fi Modal */
            background-color: #111;
            border: 2px solid #b71c1c; /* Red border */
            box-shadow: 0 0 30px rgba(183, 28, 28, 0.5); /* Red glow */
            padding: 40px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            position: relative;
        }
        .credits-content h1 {
            font-family: 'Special Elite', monospace;
            font-size: 2.5em;
            color: #b71c1c;
            margin-top: 0;
        }
        .credits-content p {
            font-family: 'Merriweather', serif;
            font-size: 1.1em;
            color: #f5eeda; /* Light text */
            line-height: 1.6;
        }
        .credits-content strong {
            font-family: 'Special Elite', monospace;
            color: #fff;
        }
        #close-credits {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 2.5em;
            color: #f5eeda; /* Light text */
            cursor: pointer;
            transition: color 0.2s ease;
        }
        #close-credits:hover {
            color: #b71c1c;
        }

    </style>
</head>
<body>

    <div id="credits-modal" class="fullscreen-container">
        <div class="credits-content">
            <span id="close-credits">&times;</span>
            <h1>Credits</h1>
            <p><strong>Game Title:</strong> The Zero Protocol</p>
            <p><strong>Submitted by:</strong> Section 5</p>
            <p><strong>Course:</strong> BSIT-2C</p>
            <br>
            <p><strong>Game Concept:</strong><br>
            A spy-themed puzzle escape room & bomb defusal twist. Players must explore a classified location, find hidden components, solve a series of analog puzzles, and escape before time runs out.
            </p>
        </div>
    </div>

    <div id="intro-screen" class="fullscreen-container">
        <div style="text-align: center;">
            <div class="intro-text">MISSION CONTROL ACCESS</div>
            <button class="btn-proceed" id="intro-continue-btn">[ Authenticate Session ]</button>
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
            
            <h1 class="hub-title">The Zero Protocol</h1>

            <div class="button-container">
                
                <div class="agent-id-card">
                    <i class="fas fa-info-circle" id="credits-button" title="Show Credits"></i>
                    
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Agent Avatar" class="agent-avatar">
                    <div class="agent-info">
                        <h1>Welcome, Agent <?php echo htmlspecialchars($username); ?></h1>
                        <p>STATUS: ACTIVE</p>
                    </div>
                </div>

                <a href="start_game.php" class="btn-menu">Begin Mission</a>
                <a href="leaderboard.php" class="btn-menu">Agent Leaderboard</a>
                
                <?php if (!$isGuest): ?>
                    <a href="profile.php" class="btn-menu">Agent Profile</a>
                    <a href="options.php" class="btn-menu">System Options</a>
                <?php endif; ?>
                
                <a href="auth_controller.php?action=logout" class="btn-menu btn-logout">Logout</a>

            </div>
        </div>

        <button id="mute-button" title="Mute">
            <i id="mute-icon" class="fas fa-volume-up"></i>
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

            // --- Credits Modal Elements ---
            const creditsButton = document.getElementById('credits-button');
            const creditsModal = document.getElementById('credits-modal');
            const closeCreditsButton = document.getElementById('close-credits');
            
            let isMuted = false;

            // Get the volume level from the cookie, default to 30
            const savedVolume = document.cookie.split('; ').find(row => row.startsWith('game_volume='))?.split('=')[1] ?? 30;
            escapeAudio.volume = savedVolume / 100;
            
            hoverAudio.volume = 0.8;
            clickAudio.volume = 0.5; 
            
            if (introButton) {
                introButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    playEscape();
                    introScreen.style.opacity = '0';
                    setTimeout(() => { introScreen.style.display = 'none'; }, 500);
                    mainContent.style.display = 'block';
                    setTimeout(() => { mainContent.style.opacity = '1'; }, 10);
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
            
            // Make sure the new credits icon also gets a hover sound
            const allButtons = document.querySelectorAll('.btn-menu, .btn-proceed, .btn-logout, #credits-button, #close-credits');
            allButtons.forEach(button => {
                button.addEventListener('mouseenter', () => {
                    hoverAudio.play();
                });
            });

            document.body.addEventListener('click', (e) => {
                // Check if the click was *not* on the credits button
                // to prevent playing the click sound twice
                if (!creditsButton.contains(e.target)) {
                     clickAudio.cloneNode(true).play();
                }
            });

            // --- Credits Modal Listeners ---
            creditsButton.addEventListener('click', (e) => {
                e.stopPropagation(); // Stop the body click sound
                clickAudio.cloneNode(true).play(); // Play click sound
                creditsModal.style.display = 'flex'; // Show the modal
            });
            closeCreditsButton.addEventListener('click', (e) => {
                e.stopPropagation(); 
                clickAudio.cloneNode(true).play();
                creditsModal.style.display = 'none'; // Hide the modal
            });
            // Also close modal if clicking on the background
            creditsModal.addEventListener('click', (e) => {
                if (e.target === creditsModal) {
                    creditsModal.style.display = 'none';
                }
            });

        });
    </script>

</body>
</html>