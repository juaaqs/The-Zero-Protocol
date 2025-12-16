<?php
// game.php
session_start();
require_once 'db_config.php';
require_once 'classes/GameSession.php';

// --- HANDLE GAME RESET ---
if (isset($_POST['reset_game'])) {
    if(isset($_SESSION['user_id'])) {
        $game = new GameSession($pdo);
        $_SESSION['game_id'] = $game->startNewGame($_SESSION['user_id'], 'easy'); 
        header("Location: game.php");
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

// --- AUTH CHECK ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['game_id'])) {
    header("Location: login.php");
    exit();
}

// --- LOAD GAME STATE ---
$game = new GameSession($pdo);
if (!$game->loadGame($_SESSION['game_id'])) {
    die("Critical Error: Link Failure. <a href='login.php'>Relogin</a>");
}

// --- PREPARE CLIENT CONFIG ---
$clientConfig = [
    'timeRemaining' => $game->getTimeRemaining(),
    'modules' => []
];

foreach ($game->modules as $m) {
    $clientConfig['modules'][] = [
        'id' => $m->id,
        'type' => $m->type,
        'isDefused' => $m->is_defused,
        'solution' => $m->solution 
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Zero Protocol // Simulation Environment</title>
    
    <link rel="stylesheet" href="assets/css/game-theme.css">
    
    <script src="https://aframe.io/releases/1.6.0/aframe.min.js"></script>
    
    <script src="assets/js/player-controls.js"></script>
    <script src="assets/js/interaction.js"></script>
    <script src="assets/js/game-core.js"></script>
</head>
<body>

    <div id="crt-overlay"></div>

    <div id="intro-overlay">
        <div id="boot-log" class="boot-sequence"></div>
        <h1 class="intro-title">ZERO PROTOCOL</h1>
        <button id="btn-start-trigger">INITIALIZE LINK</button>
    </div>

    <div id="mission-overlay">
        <div class="mission-header">PRIMARY DIRECTIVE</div>
        <div class="mission-text">
            SYSTEM STATUS: UNSTABLE<br><br>
            OBJECTIVE:<br>
            1. EXPLORE THE FACILITY TO FIND <span class="highlight">MODULES</span>.<br>
            2. SOLVE THE PUZZLES TO <span class="highlight">DEFUSE</span> THEM.<br>
            3. <span class="highlight">ESCAPE</span> BEFORE TOTAL SIGNAL LOSS.<br><br>
            LOOK AT WALLS FOR CLUES. TRUST NOTHING.
        </div>
        <button id="btn-mission-start">EXECUTE PROTOCOL</button>
    </div>

    <div id="pause-menu">
        <div class="pause-header">SYSTEM PAUSED</div>
        
        <div class="pause-content">
            <div class="pause-nav">
                <button class="nav-btn active" onclick="window.Game.switchTab('settings')">SETTINGS</button>
                <button class="nav-btn" onclick="window.Game.switchTab('controls')">CONTROLS</button>
                <button class="nav-btn" onclick="window.Game.switchTab('accessibility')">ACCESSIBILITY</button>
            </div>

            <div id="tab-settings" class="tab-pane active">
                <div class="setting-row">
                    <label>AUDIO VOLUME</label>
                    <input type="range" min="0" max="1" step="0.1" value="0.5" onchange="window.Game.setVolume(this.value)">
                </div>
                <div class="setting-row">
                    <label>MOUSE SENSITIVITY</label>
                    <input type="range" min="0.5" max="3" step="0.1" value="1.0" onchange="window.Game.setSensitivity(this.value)">
                </div>
            </div>

            <div id="tab-controls" class="tab-pane">
                <ul class="control-list">
                    <li><span>W A S D</span> MOVEMENT</li>
                    <li><span>MOUSE</span> LOOK AROUND</li>
                    <li><span>E</span> INTERACT / PICK UP</li>
                    <li><span>L</span> FLASHLIGHT</li>
                    <li><span>ESC / P</span> PAUSE MENU</li>
                </ul>
                <p class="hint-text">Find Modules on the floor. Place them on the desk. Solve the puzzle using clues found on the walls.</p>
            </div>

            <div id="tab-accessibility" class="tab-pane">
                <div class="setting-row">
                    <label>LARGE TEXT / HIGH CONTRAST</label>
                    <button class="toggle-btn" id="btn-acc-text" onclick="window.Game.toggleAccessibility('text')">OFF</button>
                </div>
                <div class="setting-row">
                    <label>REDUCED MOTION</label>
                    <button class="toggle-btn" id="btn-acc-motion" onclick="window.Game.toggleAccessibility('motion')">OFF</button>
                </div>
            </div>
        </div>

        <div class="pause-footer">
            <button class="btn-resume" onclick="window.Game.togglePause()">RESUME LINK</button>
            <button class="btn-quit" onclick="window.location.href='start_game.php'">ABORT MISSION</button>
        </div>
    </div>

    <div id="message-overlay">
        <div id="msg-title">STATUS PENDING</div>
        <div id="msg-sub">Awaiting server response...</div>
        
        <div id="end-game-buttons">
            <form method="post" id="form-restart">
                 <button type="submit" name="reset_game" class="btn-restart">RESTART MISSION</button>
            </form>
            <button id="btn-end-menu" class="btn-quit" onclick="window.location.href='start_game.php'">ABORT MISSION</button>
        </div>
    </div>

    <div id="hud">
        <div class="hud-row">TIME // <span id="timer" class="hud-val">SYNC...</span></div>
        <div class="hud-row">ERRORS // <span id="strikes" class="hud-val"></span></div>
    </div>

    <div id="interact-prompt">[E] INTERACT</div>
    
    <div id="inventory-hud">
        <div class="inv-slot" id="inv-1">EMPTY</div>
        <div class="inv-slot" id="inv-2">EMPTY</div>
    </div>

    <div id="controls-ui">
        <button id="btn-flash" class="btn-ui">LIGHT [L]</button>
    </div>

    <a-scene background="color: #050505" 
             fog="type: exponential; color: #000; density: 0.15"
             renderer="antialias: true; colorManagement: true; highRefreshRate: true;" 
             vr-mode-ui="enabled: false">
        
        <a-assets>
            <img id="tex-floor" src="assets/textures/floors.jpg">
            <img id="tex-wall" src="assets/textures/wall.jpg">
            <img id="tex-ceiling" src="assets/textures/ceiling.jpg">

            <audio id="bgm-theme" src="assets/audio/dark_ambience.mp3"></audio>
            <audio id="sfx-step" src="assets/audio/step.mp3"></audio>
            <audio id="sfx-click" src="assets/audio/ui-click.wav"></audio>

            <a-mixin id="floor-mat" material="src: #tex-floor; repeat: 4 4; roughness: 0.9; metalness: 0.1"></a-mixin>
            <a-mixin id="wall-mat" material="src: #tex-wall; repeat: 4 2; roughness: 0.8; metalness: 0"></a-mixin>
            <a-mixin id="shell-mat" material="src: #tex-ceiling; repeat: 6 6; roughness: 1; metalness: 0"></a-mixin>
        </a-assets>

        <a-box mixin="shell-mat" position="0 0 0" width="200" height="200" depth="200" side="back" color="#000"></a-box>
        
        <a-camera id="player" position="0 1.7 0" look-controls="enabled: true; pointerLockEnabled: false" wasd-controls="enabled: false" 
          player-controller flashlight-listener interaction-system>
            <a-entity id="ray-fwd" raycaster="objects: .collidable; direction: 0 0 -1; far: 0.5; interval: 50"></a-entity>
            <a-entity id="ray-bak" raycaster="objects: .collidable; direction: 0 0 1; far: 0.5; interval: 50"></a-entity>
            <a-entity id="ray-left" raycaster="objects: .collidable; direction: -1 0 0; far: 0.5; interval: 50"></a-entity>
            <a-entity id="ray-right" raycaster="objects: .collidable; direction: 1 0 0; far: 0.5; interval: 50"></a-entity>
            <a-entity raycaster="objects: .interactable; far: 3.5; interval: 100" position="0 0 0"></a-entity>
            <a-entity position="0 0 -1" geometry="primitive: ring; radiusInner: 0.005; radiusOuter: 0.008" material="color: #ffffff; shader: flat; opacity: 0.5"></a-entity>
            <a-entity id="flash-source" position="0 0 -0.2" light="type: spot; intensity: 2; angle: 45; penumbra: 0.6; distance: 25; color: #eef; castShadow: true"></a-entity>
            <a-text id="cam-msg" value="" position="0 -0.5 -1" align="center" color="#e74c3c" scale="0.4 0.4 0.4" font="exo2bold"></a-text>
        </a-camera>

        <a-light type="point" color="#fff" intensity="0.5" position="0 3 0" distance="10"></a-light>
        <a-light type="point" color="#aaf" intensity="0.3" position="0 3 15" distance="15"></a-light>
        
        <a-entity id="level-geometry">
            <a-box position="0 0 0" width="7" height="0.1" depth="7" mixin="floor-mat"></a-box>
            <a-box position="0 4 0" width="7" height="0.1" depth="7" mixin="shell-mat"></a-box>
            <a-box class="collidable" position="0 2 -3.5" width="7" height="4" depth="0.1" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-3.5 2 0" width="0.1" height="4" depth="7" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="3.5 2 0" width="0.1" height="4" depth="7" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-2.5 2 3.5" width="2" height="4" depth="0.1" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="2.5 2 3.5" width="2" height="4" depth="0.1" mixin="wall-mat"></a-box>

            <a-entity id="desk-group" position="0 0 -2">
                <a-box position="0 1 0" width="2" height="0.1" depth="1" color="#555" material="roughness: 0.6; metalness: 0.4"></a-box>
                <a-cylinder position="-0.9 0.5 -0.4" radius="0.05" height="1" color="#222"></a-cylinder>
                <a-cylinder position="0.9 0.5 -0.4" radius="0.05" height="1" color="#222"></a-cylinder>
                <a-cylinder position="-0.9 0.5 0.4" radius="0.05" height="1" color="#222"></a-cylinder>
                <a-cylinder position="0.9 0.5 0.4" radius="0.05" height="1" color="#222"></a-cylinder>
                <a-light type="spot" position="0 3 0" rotation="-90 0 0" angle="25" penumbra="0.5" intensity="2" color="#fff"></a-light>
                <a-box class="interactable" position="-0.4 1.06 0" width="0.4" height="0.02" depth="0.4" color="#3498db" material="opacity: 0.3; transparent: true" data-type="slot" data-slot-id="1">
                    <a-text value="PLACE MOD 1" align="center" color="#fff" scale="0.3 0.3 0.3" rotation="-90 0 0" position="0 0.02 0"></a-text>
                </a-box>
                <a-box class="interactable" position="0.4 1.06 0" width="0.4" height="0.02" depth="0.4" color="#3498db" material="opacity: 0.3; transparent: true" data-type="slot" data-slot-id="2">
                    <a-text value="PLACE MOD 2" align="center" color="#fff" scale="0.3 0.3 0.3" rotation="-90 0 0" position="0 0.02 0"></a-text>
                </a-box>
            </a-entity>

            <a-box position="0 0 9.25" width="3" height="0.1" depth="11.5" mixin="floor-mat"></a-box>
            <a-box position="0 4 9.25" width="3" height="0.1" depth="11.5" mixin="shell-mat"></a-box>
            <a-box class="collidable" position="-1.5 2 9.25" width="0.1" height="4" depth="11.5" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="1.5 2 9.25" width="0.1" height="4" depth="11.5" mixin="wall-mat"></a-box>

            <a-box position="0 0 27.5" width="25" height="0.1" depth="25" mixin="floor-mat"></a-box>
            <a-box position="0 4 27.5" width="25" height="0.1" depth="25" mixin="shell-mat"></a-box>
            <a-box class="collidable" position="0 2 40" width="25" height="4" depth="0.1" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-12.5 2 27.5" width="0.1" height="4" depth="25" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="12.5 2 27.5" width="0.1" height="4" depth="25" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-10 2 15" width="17.1" height="4" depth="0.1" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="10 2 15" width="17.1" height="4" depth="0.1" mixin="wall-mat"></a-box>

            <a-box class="collidable" position="-9 2 19" width="0.4" height="4" depth="12" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-5 2 28" width="0.4" height="4" depth="18" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-1 2 28" width="0.4" height="4" depth="16" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="3 2 30" width="0.4" height="4" depth="18" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="7 2 24" width="0.4" height="4" depth="16" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="11 2 30" width="0.4" height="4" depth="18" mixin="wall-mat"></a-box>

            <a-box class="collidable" position="-7 2 18" width="4" height="4" depth="0.4" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-3 2 22" width="6" height="4" depth="0.4" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="1 2 20" width="6" height="4" depth="0.4" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="5 2 26" width="6" height="4" depth="0.4" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-9 2 32" width="4" height="4" depth="0.4" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-1 2 34" width="6" height="4" depth="0.4" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="7 2 36" width="4" height="4" depth="0.4" mixin="wall-mat"></a-box>

            <a-box class="collidable" position="-11 2 26" width="3" height="4" depth="0.4" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="11 2 22" width="3" height="4" depth="0.4" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="-7 2 38" width="4" height="4" depth="0.4" mixin="wall-mat"></a-box>
            <a-box class="collidable" position="5 2 38" width="4" height="4" depth="0.4" mixin="wall-mat"></a-box>

            <a-text value="DON'T TRUST THE LIGHT" color="#333" position="-5 1.5 28.3" rotation="0 0 0" align="center" width="4"></a-text>
            <a-text value="WRONG WAY" color="#500" position="7 1.5 36.3" rotation="0 0 0" align="center" width="4"></a-text>
            <a-text value="THEY ARE WATCHING" color="#222" position="11 1.5 29" rotation="0 -90 0" align="center" width="3"></a-text>
            <a-text value="SAFE?" color="#333" position="-9 0.5 32.3" rotation="0 0 5" align="center" width="6"></a-text>
        </a-entity>

        <a-entity sound="src: #bgm-theme; autoplay: true; loop: true; volume: 0.3"></a-entity>
    </a-scene>

    <script>
        window.addEventListener('load', () => {
            const config = <?php echo json_encode($clientConfig); ?>;
            window.Game = new GameManager(config);
            window.Game.runBootSequence();
        });
    </script>
</body>
</html>