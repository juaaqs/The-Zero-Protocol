<?php
// initiate_delete.php
// This page forces the agent to confirm they want to delete their account.

session_start();
require 'db_config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Agent';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Zero Protocol - Self-Destruct Sequence</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Special+Elite&display=swap" rel="stylesheet">
    
    <script src="https://kit.fontawesome.com/3833be9c2c.js" crossorigin="anonymous"></script>
    
    <style>
        /* (CSS is the same as the profile/login pages) */
        html, body { height: 100%; margin: 0; padding: 0; font-family: 'Merriweather', serif; overflow: hidden; background-color: #000; }
        .fullscreen-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; }
        .video-background { position: fixed; top: 50%; left: 50%; min-width: 100%; min-height: 100%; width: auto; height: auto; transform: translateX(-50%) translateY(-50%); z-index: 1; pointer-events: none; }
        #bg-video { width: 100vw; height: 56.25vw; min-height: 100vh; min-width: 177.77vh; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        .video-background::after { content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.65); z-index: 2; }
        .film-grain-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-image: url('assets/images/noise-overlay.png'); animation: grain 0.4s steps(1) infinite; z-index: 3; pointer-events: none; opacity: 0.15; }
        @keyframes grain { 0% { transform: translate(0, 0); } 10% { transform: translate(-1px, -1px); } 20% { transform: translate(1px, 1px); } 30% { transform: translate(-2px, 1px); } 40% { transform: translate(2px, -1px); } 50% { transform: translate(-1px, 2px); } 60% { transform: translate(1px, -2px); } 70% { transform: translate(-2px, -2px); } 80% { transform: translate(2px, 2px); } 90% { transform: translate(-1px, 1px); } 100% { transform: translate(1px, -1px); } }

        /* --- DELETION DOSSIER --- */
        .dossier-wrapper {
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            position: fixed; top: 0; left: 0; z-index: 4;
        }
        .dossier-container {
            position: relative; z-index: 5; width: 450px;
            padding: 30px 40px; background-color: #f5eeda; 
            border: 5px solid #b71c1c; /* RED BORDER FOR DANGER */
            box-shadow: 10px 10px 25px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .dossier-title {
            font-family: 'Special Elite', monospace; font-size: 2.5em;
            color: #b71c1c; text-align: center; margin: 0 0 15px 0; text-transform: uppercase;
        }
        
        .warning-text {
            font-family: 'Merriweather', serif; font-size: 1.1em;
            color: #333; margin-bottom: 30px; line-height: 1.6;
        }
        
        .form-group { margin-bottom: 25px; }
        .form-group label {
            font-family: 'Special Elite', monospace; font-size: 1.1em;
            color: #333; display: block; margin-bottom: 8px;
        }
        .form-group input {
            font-family: 'Merriweather', serif; font-size: 1.1em;
            width: 100%; padding: 10px; box-sizing: border-box; border: none; border-bottom: 2px solid #aaa;
            background-color: rgba(210, 180, 140, 0.2);
        }

        /* --- BUTTONS --- */
        .button-group {
            display: flex; justify-content: space-between; gap: 10px;
        }
        .btn {
            display: block; flex-grow: 1; padding: 12px 5px;
            font-family: 'Special Elite', monospace; font-size: 1.2em; text-align: center;
            text-decoration: none; text-transform: uppercase; border: 2px solid #333;
            background-color: #333; color: #f5eeda; cursor: pointer; box-sizing: border-box;
            box-shadow: 0 5px 0 #1a1a1a; position: relative; top: 0;
            transition: background-color 0.2s ease, top 0.1s ease, box-shadow 0.1s ease;
        }
        .btn:hover { background-color: #000; border-color: #000; }
        .btn:active { top: 3px; box-shadow: 0 2px 0 #1a1a1a; }
        
        .btn-confirm-delete {
            background-color: #b71c1c; /* Danger Red */
            border-color: #b71c1c;
            box-shadow: 0 5px 0 #8d1616;
            color: #f5eeda;
        }
        .btn-confirm-delete:hover {
            background-color: #8d1616;
            border-color: #8d1616;
        }
        .btn-confirm-delete:active { top: 3px; box-shadow: 0 2px 0 #8d1616; }

        .btn-abort {
            background-color: transparent; color: #333; border-color: #333;
        }
        .btn-abort:hover { background-color: #333; color: #f5eeda; }
        
        /* --- Audio and Mute Button --- */
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
                
                <h1 class="dossier-title">Self-Destruct Sequence</h1>
                
                <p class="warning-text">
                    WARNING, Agent <?php echo htmlspecialchars($username); ?>. This action is permanent and irreversible. 
                    All mission data, scores, and your entire agent file will be erased.
                </p>

                <?php
                    // Display error/success messages
                    if (isset($_SESSION['message'])) {
                        $message_class = ($_SESSION['message']['type'] === 'success') ? 'success' : 'error';
                        echo '<div class="message ' . $message_class . '">' . htmlspecialchars($_SESSION['message']['text']) . '</div>';
                        unset($_SESSION['message']);
                    }
                ?>
                
                <form action="handle_delete_account.php" method="POST">
                    
                    <div class="form-group">
                        <label for="password">Confirm Passcode to Proceed:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-confirm-delete">CONFIRM DESTRUCTION</button>
                        <a href="profile.php" class="btn btn-abort">ABORT SEQUENCE</a>
                    </div>
                </form>
                
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
            
            escapeAudio.volume = 0.3;
            hoverAudio.volume = 0.8;
            clickAudio.volume = 0.5; 
            
            escapeAudio.play().catch(e => console.log("Audio play failed."));

            if (muteButton) {
                muteButton.addEventListener('click', () => {
                    const isMuted = !escapeAudio.muted;
                    escapeAudio.muted = isMuted; 
                    muteButton.title = isMuted ? 'Unmute' : 'Mute';
                    muteButton.querySelector('#mute-icon').className = isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
                });
            }
            
            const allButtons = document.querySelectorAll('.btn, .btn-abort');
            allButtons.forEach(button => {
                button.addEventListener('mouseenter', () => {
                    hoverAudio.play();
                });
            });

            document.body.addEventListener('click', () => {
                clickAudio.cloneNode(true).play();
            });
        });
    </script>

</body>
</html>