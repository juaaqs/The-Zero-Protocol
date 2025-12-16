<?php
// start_game.php - "The Zero Protocol" MISSION SELECTION
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Zero Protocol - Mission Selection</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Special+Elite&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/3833be9c2c.js" crossorigin="anonymous"></script>
    <style>
        /* (KEEP YOUR EXISTING STYLES - Just updating the HTML structure below) */
        html, body { height: 100%; margin: 0; padding: 0; font-family: 'Merriweather', serif; overflow: hidden; background-color: #000; }
        .fullscreen-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s ease-out; }
        #intro-screen { z-index: 10001; background-color: #000; padding: 20px; box-sizing: border-box; }
        .intro-text { font-family: 'Special Elite', monospace; font-size: 2.2em; color: #b71c1c; text-align: center; text-transform: uppercase; }
        .btn-proceed { font-family: 'Special Elite', monospace; font-size: 1.8em; color: #f5eeda; background: #333; border: 2px solid #f5eeda; padding: 15px 30px; cursor: pointer; margin-top: 30px; transition: background-color 0.2s ease, color 0.2s ease; }
        .btn-proceed:hover { background-color: #f5eeda; color: #333; }
        #main-content { opacity: 0; display: none; }
        .video-background { position: fixed; top: 50%; left: 50%; min-width: 100%; min-height: 100%; width: auto; height: auto; transform: translateX(-50%) translateY(-50%); z-index: 1; pointer-events: none; }
        #bg-video { width: 100vw; height: 56.25vw; min-height: 100vh; min-width: 177.77vh; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        .video-background::after { content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.65); z-index: 2; }
        .film-grain-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-image: url('assets/images/noise-overlay.png'); animation: grain 0.4s steps(1) infinite; z-index: 3; pointer-events: none; opacity: 0.15; }
        @keyframes grain { 0% { transform: translate(0, 0); } 10% { transform: translate(-1px, -1px); } 100% { transform: translate(1px, -1px); } }
        .dossier-wrapper { width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; z-index: 4; padding: 20px; box-sizing: border-box; }
        .mission-board { position: relative; z-index: 5; width: 90%; max-width: 1000px; padding: 30px 40px; background-color: #f5eeda; border: 1px solid #c9b79c; box-shadow: 10px 10px 25px rgba(0, 0, 0, 0.5); }
        .mission-board-title { font-family: 'Special Elite', monospace; font-size: 3em; color: #2F2F2F; text-align: center; margin: 0 0 30px 0; text-transform: uppercase; border-bottom: 2px solid #aaa; padding-bottom: 15px; }
        .mission-grid { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; }
        .mission-card { width: 300px; background: #e6dace; border: 1px solid #c9b79c; box-shadow: 4px 4px 10px rgba(0,0,0,0.3); text-align: left; transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column; }
        .mission-card:hover { transform: translateY(-5px); box-shadow: 8px 8px 20px rgba(0,0,0,0.4); }
        .mission-card-header { padding: 15px 20px; background: rgba(0,0,0,0.05); border-bottom: 1px dashed #c9b79c; }
        .mission-card-header h2 { font-family: 'Special Elite', monospace; font-size: 1.6em; color: #2F2F2F; margin: 0 0 5px 0; }
        .mission-card-header p { font-family: 'Merriweather', serif; font-size: 1.1em; color: #b71c1c; margin: 0; font-weight: bold; }
        .mission-card-body { padding: 20px; font-family: 'Merriweather', serif; font-size: 1em; color: #333; line-height: 1.6; flex-grow: 1; }
        .mission-card-footer { padding: 0 20px 20px 20px; }
        .btn-launch { display: block; width: 100%; padding: 12px; font-family: 'Special Elite', monospace; font-size: 1.3em; text-align: center; text-decoration: none; text-transform: uppercase; border: 2px solid #333; background-color: #333; color: #f5eeda; cursor: pointer; box-sizing: border-box; box-shadow: 0 5px 0 #1a1a1a; position: relative; top: 0; transition: background-color 0.2s ease, top 0.1s ease, box-shadow 0.1s ease; }
        .btn-launch:hover { background-color: #000; border-color: #000; }
        .btn-launch:active { top: 3px; box-shadow: 0 2px 0 #1a1a1a; }
        .btn-locked { background-color: #aaa; border-color: #777; color: #f5eeda; box-shadow: 0 5px 0 #555; cursor: not-allowed; }
        .btn-locked:hover { background-color: #aaa; border-color: #777; }
        
        .btn-back { font-family: 'Special Elite', monospace; font-size: 1.2em; color: #333; text-decoration: none; padding: 5px 10px; border: 1px solid #c9b79c; transition: background-color 0.2s ease, color 0.2s ease; }
        .btn-back:hover { background-color: #333; color: #f5eeda; }
        
        #mute-button { position: fixed; bottom: 20px; right: 20px; z-index: 1000; background-color: #333; border: 2px solid #f5eeda; color: #f5eeda; border-radius: 50%; width: 55px; height: 55px; font-size: 1.6em; cursor: pointer; display: flex; justify-content: center; align-items: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5); transition: background-color 0.2s ease, transform 0.2s ease; }
        #mute-button:hover { background-color: #000; transform: scale(1.1); }
    </style>
</head>
<body>

    <div id="intro-screen" class="fullscreen-container">
        <div style="text-align: center;">
            <div class="intro-text">LOADING MISSION FILES...</div>
            <button class="btn-proceed" id="intro-continue-btn">[ Access Briefing Room ]</button>
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
            <div class="mission-board">
                
                <div style="position: absolute; top: 30px; left: 30px; display: flex; gap: 10px; z-index: 6;">
                    <a href="menu.php" class="btn-back" style="position: static;">&larr; Hub</a>
                    <a href="leaderboard.php" class="btn-back" style="position: static;">Global Rankings</a>
                </div>
                
                <h1 class="mission-board-title">Classified Missions</h1>
                
                <div class="mission-grid">
                
                    <div class="mission-card">
                        <div class="mission-card-header">
                            <h2>The Berlin Safehouse</h2>
                            <p>DIFFICULTY: EASY</p>
                        </div>
                        <div class="mission-card-body">
                            <p><strong>Intel:</strong> A training scenario. Infiltrate a Cold War-era safehouse.</p>
                            <ul>
                                <li><strong>Objective:</strong> 2 Modules</li>
                                <li><strong>Time Limit:</strong> 3:00</li>
                            </ul>
                        </div>
                        <div class="mission-card-footer">
                            <a href="handle_start_game.php?difficulty=easy" class="btn-launch" id="launch-easy">Launch Mission</a>
                        </div>
                    </div>
                    
                    <div class="mission-card">
                        <div class="mission-card-header">
                            <h2>The Cairo Dig-Site</h2>
                            <p>DIFFICULTY: MEDIUM</p>
                        </div>
                        <div class="mission-card-body">
                            <p><strong>Intel:</strong> An active dig-site has been compromised. A tool is required.</p>
                            <ul>
                                <li><strong>Objective:</strong> 3 Modules</li>
                                <li><strong>Time Limit:</strong> 07:00</li>
                            </ul>
                        </div>
                        <div class="mission-card-footer">
                           <a href="handle_start_game.php?difficulty=medium" class="btn-launch" id="launch-medium">Launch Mission</a>
                        </div>
                    </div>
                    
                    <div class="mission-card">
                        <div class="mission-card-header">
                            <h2>The Kremlin's Shadow</h2>
                            <p>DIFFICULTY: HARD</p>
                        </div>
                        <div class="mission-card-body">
                            <p><strong>Intel:</strong> Deep cover operation. The device is secured inside a safe.</p>
                            <ul>
                                <li><strong>Objective:</strong> 3 Modules</li>
                                <li><strong>Time Limit:</strong> 05:00</li>
                            </ul>
                        </div>
                        <div class="mission-card-footer">
                             <a href="handle_start_game.php?difficulty=hard" class="btn-launch" id="launch-hard">Launch Mission</a>
                        </div>
                    </div>
                
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
            
            const allButtons = document.querySelectorAll('.btn-launch, .btn-proceed, .btn-back');
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