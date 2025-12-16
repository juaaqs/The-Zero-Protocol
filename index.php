<?php
// index.php - "The Zero Protocol" SPLASH SCREEN
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Zero Protocol - Classified</title>
    
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
            flex-direction: column; /* Center vertically */
            transition: opacity 0.5s ease-out;
        }
        
        /* --- INTRO SCREEN (Starts visible) --- */
        #intro-screen {
            z-index: 10001;
            background-color: #000;
        }
        .intro-text {
            font-family: 'Special Elite', monospace;
            font-size: 2.2em;
            color: #b71c1c;
            text-align: center;
            text-transform: uppercase;
            text-shadow: 0 0 10px #b71c1c; /* Red glow */
        }
        
        /* --- PRELOADER (Starts hidden) --- */
        #preloader {
            z-index: 10000;
            background-color: #000;
            display: none; /* Hidden by default */
            opacity: 0;
        }
        
        #preloader-text {
            font-family: 'Special Elite', monospace;
            font-size: 2em;
            color: #b71c1c;
            letter-spacing: 2px;
            text-shadow: 0 0 10px #b71c1c;
            animation: blink 1.2s infinite;
        }

        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }
        
        /* --- MAIN CONTENT (Starts hidden) --- */
        #main-content {
            opacity: 0;
            display: none; 
        }

        /* --- LAYER 1: VIDEO BACKGROUND --- */
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
            width: 100vw;
            height: 56.25vw; /* 16:9 */
            min-height: 100vh;
            min-width: 177.77vh;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .video-background::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.65);
            z-index: 2;
        }

        /* --- LAYER 3: FILM GRAIN OVERLAY --- */
        .film-grain-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('assets/images/noise-overlay.png');
            animation: grain 0.4s steps(1) infinite;
            z-index: 3;
            pointer-events: none;
            opacity: 0.15;
        }
        
        @keyframes grain {
            0% { transform: translate(0, 0); } 10% { transform: translate(-1px, -1px); }
            20% { transform: translate(1px, 1px); } 30% { transform: translate(-2px, 1px); }
            40% { transform: translate(2px, -1px); } 50% { transform: translate(-1px, 2px); }
            60% { transform: translate(1px, -2px); } 70% { transform: translate(-2px, -2px); }
            80% { transform: translate(2px, 2px); } 90% { transform: translate(-1px, 1px); }
            100% { transform: translate(1px, -1px); }
        }

        /* --- LAYER 2: Splash Screen Content --- */
        .splash-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column; /* Stack title and button */
            position: fixed;
            top: 0;
            left: 0;
            z-index: 4;
        }
        
        /* --- Big Red Elegant Title --- */
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
        
        /* --- New Subtitle Style --- */
        .hub-subtitle {
            font-family: 'Merriweather', serif;
            font-size: 1.3em;
            color: #f5eeda; /* Faded white text */
            text-transform: uppercase;
            letter-spacing: 5px; /* Wide spacing */
            text-shadow: 0 0 5px rgba(245, 238, 218, 0.5);
            margin-top: -15px; /* Tucks it under the main title */
            margin-bottom: 30px;
            z-index: 5;
            text-align: center;
        }

        /* --- Sci-Fi Button Style (Red) --- */
        .btn-menu, .btn-proceed {
            display: block;
            width: 450px; /* Match menu width */
            padding: 15px 12px;
            margin-bottom: 15px;
            font-family: 'Special Elite', monospace; font-size: 1.5em;
            text-align: center; text-decoration: none;
            text-transform: uppercase; 
            
            /* New Red Palette */
            background-color: rgba(183, 28, 28, 0.1); /* Faint red bg */
            border: 1px solid #b71c1c; /* Red border */
            color: #b71c1c; /* Red text */
            text-shadow: 0 0 10px rgba(183, 28, 28, 0.7); /* Red glow */
            
            cursor: pointer; box-sizing: border-box;
            position: relative; top: 0;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-menu:hover, .btn-proceed:hover { 
            background-color: rgba(183, 28, 28, 0.3);
            color: #f5eeda; /* Light text on hover */
            box-shadow: 0 0 15px #b71c1c;
        }
        .btn-menu:active, .btn-proceed:active { 
            top: 1px;
            background-color: rgba(183, 28, 28, 0.5);
        }
        
        /* --- Mute Button --- */
        #mute-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: #333;
            border: 2px solid #f5eeda;
            color: #f5eeda;
            border-radius: 50%;
            width: 55px;
            height: 55px;
            font-size: 1.6em;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        #mute-button:hover {
            background-color: #000;
            transform: scale(1.1);
        }
    </style>
</head>
<body>

    <div id="intro-screen" class="fullscreen-container">
        <div>
            <div class="intro-text">ACCESSING TERMINAL...</div>
            <a href="#" class="btn-proceed" id="intro-continue-btn" style="margin-top: 30px;">[ Click to Proceed ]</a>
        </div>
    </div>

    <div id="preloader" class="fullscreen-container">
        <div id="preloader-text">LOADING...</div>
    </div>

    <div id="main-content" class="fullscreen-container">

        <div class="video-background">
            <video autoplay muted loop playsinline id="bg-video">
                <source src="assets/video/bg-video.mp4" type="video/mp4">
            </video>
        </div>

        <div class="film-grain-overlay"></div>

        <div class="splash-wrapper">
            
            <h1 class="hub-title">The Zero Protocol</h1>
            <p class="hub-subtitle">CLASSIFIED AGENT TERMINAL</p>
            
            <a href="login.php" class="btn-menu" id="play-button">Begin Mission</a>

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
            
            // --- Get All Elements ---
            const introScreen = document.getElementById('intro-screen');
            const introButton = document.getElementById('intro-continue-btn');
            const preloader = document.getElementById('preloader');
            const mainContent = document.getElementById('main-content');
            
            const escapeAudio = document.getElementById('audio-escape');
            const hoverAudio = document.getElementById('audio-hover');
            const clickAudio = document.getElementById('audio-click');
            const muteButton = document.getElementById('mute-button');
            const muteIcon = document.getElementById('mute-icon');
            const playButton = document.getElementById('play-button');

            // --- Audio State ---
            let isMuted = false;
            
            // Get the volume level from the cookie, default to 30
            const savedVolume = document.cookie.split('; ').find(row => row.startsWith('game_volume='))?.split('=')[1] ?? 30;
            escapeAudio.volume = savedVolume / 100;

            hoverAudio.volume = 0.8;
            clickAudio.volume = 0.5; 
            
            // --- Main Flow ---
            introButton.addEventListener('click', (e) => {
                e.preventDefault();
                playEscape(); // Start audio on first click
                
                // 1. Fade out Intro Screen
                introScreen.style.opacity = '0';
                setTimeout(() => { introScreen.style.display = 'none'; }, 500);

                // 2. Show Preloader
                preloader.style.display = 'flex';
                setTimeout(() => { preloader.style.opacity = '1'; }, 10);

                // 3. Run Preloader
                setTimeout(() => {
                    // 4. Fade out Preloader
                    preloader.style.opacity = '0';
                    
                    // 5. Fade in Main Content
                    mainContent.style.display = 'flex';
                    setTimeout(() => {
                        mainContent.style.opacity = '1';
                    }, 10);
                    
                    // 6. Hide Preloader after fade
                    setTimeout(() => {
                        preloader.style.display = 'none';
                    }, 500);

                }, 2000); // 2 seconds
            });

            // --- Audio Functions ---
            function playEscape() {
                if (escapeAudio.paused) {
                    escapeAudio.play().catch(e => console.log("Audio play failed."));
                }
                escapeAudio.muted = isMuted;
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
            
            // --- UI Sound Listeners ---
            if (playButton) {
                playButton.addEventListener('mouseenter', () => {
                    if (!isMuted) hoverAudio.play();
                });
            }
            if (muteButton) {
                 muteButton.addEventListener('mouseenter', () => {
                    if (!isMuted) hoverAudio.play();
                });
            }
            if (introButton) {
                 introButton.addEventListener('mouseenter', () => {
                    if (!isMuted) hoverAudio.play();
                });
            }

            // Global click sound
            document.body.addEventListener('click', (e) => {
                // Don't play click sound for the intro button
                if (e.target.closest('#intro-continue-btn')) {
                    return;
                }
                if (!isMuted) clickAudio.cloneNode(true).play();
            });
        });
    </script>

</body>
</html>