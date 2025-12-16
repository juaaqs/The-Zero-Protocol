<?php
session_start();

// 1. --- SESSION PROTECTION ---
// Protect the page: If no game is starting, go back to menu.
if (!isset($_SESSION['game_id'])) {
    header('Location: menu.php');
    exit;
}

// Get the username for the intro text
$username = $_SESSION['username'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D-Fuse-It! - Mission Start</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #000;
            color: #FFF;
            font-family: 'Share Tech Mono', monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            text-align: center;
        }
        #intro-container {
            width: 80%;
            max-width: 800px;
        }
        .intro-text {
            font-size: 2.2em;
            margin: 0;
            opacity: 0; /* Start hidden */
            transition: opacity 1.0s ease-in-out;
        }
    </style>
</head>
<body>

    <div id="intro-container">
        <p id="intro-line" class="intro-text"></p>
    </div>

    <script>
        // --- 1. CONFIGURATION ---
        const lines = [
            "AGENT: [<?php echo htmlspecialchars($username); ?>]",
            "STATUS: [CLASSIFIED]",
            "...",
            "A critical threat has been detected in your sector.",
            "Your mission is to infiltrate the location...",
            "Find the device...",
            "And defuse it.",
            "Failure is not an option.",
            "Good luck, agent."
        ];

        // Time (in milliseconds) for each line
        const displayTime = 3000; // 3 seconds per line
        const fadeTime = 1000;    // 1 second to fade
        const redirectTo = 'game.php'; // The page to go to after

        // --- 2. THE INTRO FUNCTION ---
        const textElement = document.getElementById('intro-line');

        // Helper function to pause
        function wait(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        // Main function to run the intro
        async function runIntro() {
            for (const line of lines) {
                // Set text and fade in
                textElement.textContent = line;
                textElement.style.opacity = 1;

                // Wait for the display time
                await wait(displayTime);

                // Fade out
                textElement.style.opacity = 0;

                // Wait for fade to finish
                await wait(fadeTime);
            }

            // After all lines, redirect to the game
            window.location.href = redirectTo;
        }

        // --- 3. START THE INTRO ---
        // Wait a moment for the page to render before starting
        window.onload = () => {
            setTimeout(runIntro, 500);
        };
    </script>
</body>
</html>