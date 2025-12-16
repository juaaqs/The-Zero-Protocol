<?php
// options.php - "The Zero Protocol" SYSTEM OPTIONS
session_start();

// If the user isn't logged in, send them away.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// We assume default volume is 0.3 (30%) if not set in a cookie or DB
$current_volume = $_COOKIE['game_volume'] ?? 30; // Use a cookie for persistence
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Zero Protocol - System Options</title>
    
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

        /* --- General Theming (Copied from profile.php) --- */
        .fullscreen-container {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
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

        /* --- OPTIONS DOSSIER --- */
        .dossier-wrapper {
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            position: fixed; top: 0; left: 0; z-index: 4;
        }
        .dossier-panel {
            position: relative; z-index: 5; max-width: 500px;
            width: 90%; padding: 40px; background-color: #f5eeda;
            border: 5px double #333;
            box-shadow: 10px 10px 25px rgba(0, 0, 0, 0.5);
        }
        
        .dossier-title {
            font-family: 'Special Elite', monospace; font-size: 2.5em;
            color: #2F2F2F; text-align: center;
            margin: 0 0 15px 0; text-transform: uppercase;
        }
        
        .options-section {
            border-top: 1px dashed #aaa;
            padding-top: 25px;
            margin-top: 25px;
        }
        
        /* --- SLIDER/VOLUME CONTROL --- */
        .volume-control {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        .volume-control i {
            font-size: 1.5em;
            color: #333;
        }
        .volume-slider {
            flex-grow: 1;
        }
        .volume-slider input[type=range] {
            width: 100%;
            height: 8px;
            background: #d2b48c; /* Tan track */
            border-radius: 5px;
            -webkit-appearance: none;
            appearance: none;
        }
        .volume-slider input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            background: #b71c1c; /* Red thumb */
            border: 1px solid #333;
            border-radius: 50%;
            cursor: pointer;
        }
        .volume-value {
            font-family: 'Special Elite', monospace;
            width: 40px;
            text-align: right;
            font-size: 1.2em;
            color: #b71c1c;
        }

        /* --- Button and Link Styles --- */
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

        .btn-back {
            position: absolute; top: 30px; left: 30px;
            font-family: 'Special Elite', monospace; font-size: 1.2em;
            color: #f5eeda; text-decoration: none; padding: 5px 10px;
            border: 1px solid #f5eeda; background-color: rgba(0, 0, 0, 0.5);
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s;
            z-index: 6;
        }
        .btn-back:hover { background-color: #f5eeda; color: #333; border-color: #333; }
        
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
        
        <a href="menu.php" class="btn-back" id="back-button">&larr; Return to Hub</a>

        <div class="dossier-wrapper">
            <div class="dossier-panel">
                
                <h1 class="dossier-title">System Options</h1>
                
                <form action="handle_save_options.php" method="POST">
                    
                    <div class="options-section">
                        <h2 style="font-family: 'Special Elite', monospace; font-size: 1.6em; margin-top: 0;">Audio Configuration</h2>

                        <div class="volume-control">
                            <i class="fas fa-volume-down"></i>
                            <div class="volume-slider">
                                <input type="range" id="volume-range" name="volume" min="0" max="100" value="<?php echo htmlspecialchars($current_volume); ?>">
                            </div>
                            <i class="fas fa-volume-up"></i>
                            <span class="volume-value" id="volume-display"><?php echo htmlspecialchars($current_volume); ?>%</span>
                        </div>
                        
                        <p style="font-size: 0.9em; color: #555; text-align: center;">Note: This controls the background ambiance (escape.mp3).</p>
                    </div>
                    
                    <div class="options-section">
                        <h2 style="font-family: 'Special Elite', monospace; font-size: 1.6em; margin-top: 0;">Display Settings</h2>
                        
                        <p style="font-size: 0.9em; color: #333;">(Future settings like resolution or graphical detail would go here.)</p>
                    </div>
                    
                    <button type="submit" class="btn">Save Configuration</button>
                </form>

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
            
            // --- UI/Volume Slider Logic ---
            const volumeRange = document.getElementById('volume-range');
            const volumeDisplay = document.getElementById('volume-display');
            const escapeAudio = document.getElementById('audio-escape');
            
            // Initialize audio volume based on the slider value (0-100 to 0.0-1.0)
            escapeAudio.volume = volumeRange.value / 100;
            
            // Listener to update display and audio in real-time
            volumeRange.addEventListener('input', () => {
                const newVolume = volumeRange.value;
                volumeDisplay.textContent = newVolume + '%';
                escapeAudio.volume = newVolume / 100;

                // Mute button icon toggle logic (if volume hits 0)
                const muteIcon = document.getElementById('mute-icon');
                if (newVolume == 0) {
                    muteIcon.className = 'fas fa-volume-off';
                    escapeAudio.muted = true;
                } else if (escapeAudio.muted) {
                    muteIcon.className = 'fas fa-volume-up';
                    escapeAudio.muted = false; // Unmute if volume is moved up
                }
            });


            // --- Audio and Global Click Logic ---
            const hoverAudio = document.getElementById('audio-hover');
            const clickAudio = document.getElementById('audio-click');
            const muteButton = document.getElementById('mute-button');
            
            escapeAudio.play().catch(e => console.log("Audio play failed."));

            if (muteButton) {
                muteButton.addEventListener('click', () => {
                    const isMuted = !escapeAudio.muted;
                    escapeAudio.muted = isMuted; 
                    muteButton.title = isMuted ? 'Unmute' : 'Mute';
                    // Use a different icon for temporary mute vs volume=0
                    muteButton.querySelector('#mute-icon').className = isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
                });
            }
            
            const allElements = document.querySelectorAll('.btn, .btn-back');
            allElements.forEach(element => {
                element.addEventListener('mouseenter', () => {
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