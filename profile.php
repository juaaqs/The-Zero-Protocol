<?php
// profile.php - "The Zero Protocol" AGENT DOSSIER
session_start();
require 'db_config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
// FIX: Add cache-busting timestamp to avatar URL to prevent blurriness/caching
$avatar_url = $_SESSION['avatar_url'] . '?' . time();
$isGuest = $_SESSION['is_guest'] ?? false;

// Determine which tab to show on load
$show_security_tab = isset($_SESSION['message']);

$user_email = '';
$is_2fa_enabled = false;

if (!$isGuest) {
    try {
        $stmt = $pdo->prepare("SELECT email, is_2fa_enabled FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $user_email = $user_data['email'];
            $is_2fa_enabled = (bool)$user_data['is_2fa_enabled'];
        }
    } catch (PDOException $e) {
        error_log("Profile load error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Zero Protocol - Agent Dossier</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Special+Elite&display=swap" rel="stylesheet">
    
    <script src="https://kit.fontawesome.com/3833be9c2c.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>
    
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

        /* --- DOSSIER CONTAINER --- */
        .dossier-wrapper {
            width: 100%; height: 100%; display: flex;
            justify-content: center; align-items: center;
            position: fixed; top: 0; left: 0; z-index: 4;
            /* Allow scrolling *within* the dossier if content is tall */
            overflow-y: auto; 
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .dossier-panel {
            position: relative; z-index: 5; max-width: 600px;
            width: 90%; background-color: #f5eeda;
            border: 5px double #333;
            box-shadow: 10px 10px 25px rgba(0, 0, 0, 0.5);
            min-height: 400px;
            margin: auto; /* Center it */
        }
        
        /* --- DOSSIER TABS --- */
        .dossier-tabs {
            display: flex;
            border-bottom: 2px solid #333;
            background-color: #e6dace;
        }
        .dossier-tab {
            font-family: 'Special Elite', monospace;
            padding: 15px 25px;
            cursor: pointer;
            border-right: 1px solid #c9b79c;
            color: #555;
            transition: background-color 0.2s, color 0.2s;
        }
        .dossier-tab:hover {
            background-color: #ddd;
            color: #222;
        }
        .dossier-tab.active {
            background-color: #f5eeda;
            border-bottom: 2px solid #f5eeda;
            color: #b71c1c;
        }
        
        /* --- CONTENT AREA --- */
        .dossier-content {
            padding: 30px;
        }
        .content-section {
            display: none;
        }
        .content-section.active {
            display: block;
        }
        
        /* --- FORM STYLES --- */
        .form-group { margin-bottom: 25px; }
        .form-group label {
            font-family: 'Special Elite', monospace; font-size: 1.1em;
            color: #333; display: block; margin-bottom: 8px;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            font-family: 'Merriweather', serif; font-size: 1.1em;
            width: 100%; padding: 10px; box-sizing: border-box;
            border: none; border-bottom: 2px solid #aaa;
            background-color: rgba(210, 180, 140, 0.2);
        }
        
        /* --- AVATAR UPLOAD --- */
        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid #b71c1c;
            object-fit: cover;
            margin: 10px auto 20px 0;
            display: block;
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

        /* --- TOGGLE SWITCH (2FA) --- */
        .toggle-label {
            position: relative; display: inline-block;
            width: 60px; height: 34px;
        }
        .toggle-label input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc; transition: .4s; border-radius: 34px;
        }
        .slider:before {
            position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px;
            background-color: white; transition: .4s; border-radius: 50%;
        }
        input:checked + .slider { background-color: #b71c1c; }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .toggle-text {
            font-family: 'Special Elite', monospace;
            margin-left: 70px;
            line-height: 34px;
            color: #b71c1c;
            font-weight: bold;
        }

        /* --- Button Styles --- */
        .btn {
            display: block; width: 100%; padding: 12px; margin-top: 15px;
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
        
        .message {
            font-family: 'Special Elite', monospace; font-size: 1.1em;
            text-align: center; margin-bottom: 15px; border: 1px dashed; padding: 10px;
            background-color: rgba(0,0,0, 0.05);
        }
        .message.success { color: #556B2F; border-color: #556B2F; background-color: rgba(85, 107, 47, 0.05); }
        .message.error { color: #b71c1c; border-color: #b71c1c; background-color: rgba(183, 28, 28, 0.05); }
        
        /* --- NEW MODAL STYLES FOR CROPPING --- */
        #upload-modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.8); z-index: 10000;
        }
        .modal-content {
            background-color: #f5eeda; padding: 20px; border: 5px double #b71c1c;
            width: 90%; max-width: 500px; text-align: center;
        }
        #crop-area {
            width: 100%; height: 350px; border: 1px solid #ccc;
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

    <div id="upload-modal" class="fullscreen-container">
        <div class="modal-content">
            <h2 style="font-family: 'Special Elite', monospace;">Adjust Dossier Photo</h2>
            <div id="crop-area"></div>
            <button class="btn" id="crop-button" style="margin-top: 20px;">Confirm & Upload</button>
            <button class="btn" id="cancel-button" style="margin-top: 10px; background-color: transparent; color: #333; border-color: #333;">Cancel</button>
        </div>
    </div>

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
                
                <div class="dossier-tabs">
                    <div class="dossier-tab <?php echo $show_security_tab ? '' : 'active'; ?>" id="tab-profile">AGENT PROFILE</div>
                    <div class="dossier-tab <?php echo $show_security_tab ? 'active' : ''; ?>" id="tab-security">ACCOUNT SECURITY</div>
                </div>
                
                <div class="dossier-content">
                    <?php
                        // Handle messages (e.g., from handle_change_password.php)
                        if (isset($_SESSION['message'])) {
                            $message_type = $_SESSION['message']['type'];
                            $message_text = $_SESSION['message']['text'];
                            echo '<div class="message ' . $message_type . '">' . htmlspecialchars($message_text) . '</div>';
                            unset($_SESSION['message']);
                        }
                    ?>

                    <div class="content-section <?php echo $show_security_tab ? '' : 'active'; ?>" id="profile-content">
                        
                        <h1 style="font-family: 'Special Elite', monospace; margin-top: 0;">Callsign: <?php echo htmlspecialchars($username); ?></h1>
                        
                        <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Agent Avatar" class="avatar-preview" id="avatar-preview-img">
                        
                        <form action="handle_avatar_upload.php" method="POST" id="avatar-form">
                            <div class="form-group">
                                <label for="avatar_file">Change Avatar:</label>
                                <input type="file" id="avatar_file" accept="image/*" style="display: none;">
                                <input type="hidden" name="cropped_image_data" id="cropped_image_data">
                            </div>
                            <button type="button" class="btn" id="trigger-crop-button">Select Photo & Adjust</button>
                        </form>
                        
                    </div>

                    <div class="content-section <?php echo $show_security_tab ? 'active' : ''; ?>" id="security-content">
                        
                        <h1 style="font-family: 'Special Elite', monospace; margin-top: 0;">Passcode Management</h1>

                        <form action="handle_change_password.php" method="POST">
                            <div class="form-group">
                                <label for="current_password">Current Passcode:</label>
                                <div class="password-wrapper">
                                    <input type="password" id="current_password" name="current_password" required>
                                    <button type="button" class="toggle-password" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Passcode:</label>
                                <div class="password-wrapper">
                                    <input type="password" id="new_password" name="new_password" required>
                                    <button type="button" class="toggle-password" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <button type="submit" class="btn">Update Passcode</button>
                        </form>
                        
                        <hr style="margin: 30px 0; border-top: 1px dashed #aaa;">

                        <h1 style="font-family: 'Special Elite', monospace;">Two-Factor Access (2FA)</h1>

                        <?php if ($isGuest): ?>
                             <p class="message error">2FA is not available for Anonymous Entry.</p>
                        <?php else: ?>
                            <div class="form-group">
                                <label class="toggle-label">
                                    <input type="checkbox" id="2fa-toggle" <?php echo $is_2fa_enabled ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <span class="toggle-text" style="color: <?php echo $is_2fa_enabled ? '#b71c1c' : '#333'; ?>;" id="2fa-status">
                                    <?php echo $is_2fa_enabled ? 'STATUS: ACTIVE' : 'STATUS: INACTIVE'; ?>
                                </span>
                            </div>
                            <p style="font-size: 0.9em; color: #555;">Your verification email is: <?php echo htmlspecialchars($user_email); ?></p>
                        <?php endif; ?>
                        
                        <a href="initiate_delete.php" class="btn" style="background-color: #b71c1c; margin-top: 25px;">Self-Destruct Account</a>

                    </div>
                    
                </div>
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
            
            // --- Tab Switching Logic ---
            const tabProfile = document.getElementById('tab-profile');
            const tabSecurity = document.getElementById('tab-security');
            const contentProfile = document.getElementById('profile-content');
            const contentSecurity = document.getElementById('security-content');
            const showSecurityTab = <?php echo $show_security_tab ? 'true' : 'false'; ?>;

            function showTab(tabId) {
                const isProfile = (tabId === 'profile');
                tabProfile.classList.toggle('active', isProfile);
                tabSecurity.classList.toggle('active', !isProfile);
                contentProfile.classList.toggle('active', isProfile);
                contentSecurity.classList.toggle('active', !isProfile);
            }

            // Show the correct tab on page load
            if (showSecurityTab) {
                showTab('security');
            } else {
                showTab('profile');
            }
            
            tabProfile.addEventListener('click', () => showTab('profile'));
            tabSecurity.addEventListener('click', () => showTab('security'));

            // --- Cropping/Avatar Logic ---
            const uploadFile = document.getElementById('avatar_file');
            const uploadModal = document.getElementById('upload-modal');
            const cropArea = document.getElementById('crop-area');
            const cropButton = document.getElementById('crop-button');
            const cancelButton = document.getElementById('cancel-button');
            const croppedImageData = document.getElementById('cropped_image_data');
            const avatarForm = document.getElementById('avatar-form');
            const triggerCropButton = document.getElementById('trigger-crop-button');
            const avatarPreviewImg = document.getElementById('avatar-preview-img');

            let croppie = null; 

            uploadFile.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        uploadModal.style.display = 'flex';
                        if (croppie) { croppie.destroy(); } // Destroy old instance

                        // Initialize Croppie
                        croppie = new Croppie(cropArea, {
                            // FIX: Enlarged viewport for easier cropping
                            viewport: { width: 350, height: 350, type: 'circle' }, 
                            boundary: { width: '100%', height: 350 },
                            enableOrientation: true
                        });
                        croppie.bind({
                            url: event.target.result
                        });
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Trigger the hidden file input
            triggerCropButton.addEventListener('click', () => {
                uploadFile.click();
            });

            // Handle the crop and submit button
            cropButton.addEventListener('click', function() {
                croppie.result({
                    type: 'base64',
                    // FIX: Request a high-quality 300x300 image
                    size: { width: 300, height: 300 }, 
                    format: 'jpeg',
                    quality: 1 // Max quality
                }).then(function(base64) {
                    croppedImageData.value = base64; 
                    avatarForm.submit();
                });
            });

            // Handle cancel button
            cancelButton.addEventListener('click', function() {
                uploadModal.style.display = 'none';
                if (croppie) {
                    croppie.destroy();
                    croppie = null;
                }
            });


            // --- 2FA Toggle Logic ---
            const toggle2FA = document.getElementById('2fa-toggle');
            const status2FA = document.getElementById('2fa-status');

            if (toggle2FA) {
                toggle2FA.addEventListener('change', () => {
                    const isActive = toggle2FA.checked;
                    status2FA.textContent = isActive ? 'STATUS: ACTIVE' : 'STATUS: INACTIVE';
                    status2FA.style.color = isActive ? '#b71c1c' : '#333';

                    fetch('handle_toggle_2fa.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'status=' + (isActive ? 'enable' : 'disable')
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status !== 'success') {
                            // Revert on failure
                            toggle2FA.checked = !isActive;
                            status2FA.textContent = isActive ? 'STATUS: INACTIVE' : 'STATUS: ACTIVE';
                            status2FA.style.color = isActive ? '#333' : '#b71c1c';
                            alert('Failed to update 2FA status: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Network Error:', error);
                        toggle2FA.checked = !isActive;
                        status2FA.textContent = isActive ? 'STATUS: INACTIVE' : 'STATUS: ACTIVE';
                        status2FA.style.color = isActive ? '#333' : '#b71c1c';
                        alert('A network error occurred. Please try again.');
                    });
                });
            }
            
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


            // --- Audio and Global Click Logic ---
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
            
            const allElements = document.querySelectorAll('.dossier-tab, .btn, .btn-back, .toggle-password, .slider');
            allElements.forEach(element => {
                element.addEventListener('mouseenter', () => {
                    if (!isMuted) hoverAudio.play();
                });
            });

            document.body.addEventListener('click', () => {
                if (!isMuted) clickAudio.cloneNode(true).play();
            });
        });
    </script>

</body>
</html>