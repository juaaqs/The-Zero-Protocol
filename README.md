# The-Zero-Protocol

# Project Overview
Game Description: The Zero Protocol
The Zero Protocol is a first-person, spy-themed puzzle escape game built with PHP and A-Frame (JavaScript).

You play as an operative infiltrating a dark, unstable facility. Your mission is to locate hidden components, solve analog puzzles to defuse them, and escape before you lose the signal or run out of time. The game features a leaderboard system, varying difficulty levels (Easy, Medium, Hard), and an immersive "retro-tech" atmosphere with film grain and CRT effects.

# Technology Stacks
Core Stack
**Backend Language**: PHP 8+ (Uses strictly typed OOP classes like GameSession.php, Player.php).
**Database**: MySQL (connected via PDO for security).
**Frontend Logic**: Vanilla JavaScript (ES6+).
**3D Engine**: A-Frame (WebVR framework based on Three.js).
**Styling**: CSS3 with CSS Variables (Custom retro-spy theme).

Libraries & APIs
**Google OAuth 2.0**: Used for the "Sign in with Google" feature (auth_controller.php).
**PHPMailer**: Used for sending 2FA codes and password reset links (email_helper.php).
**FontAwesome**: Used for UI icons (locks, volume controls, eyes).
**Google Fonts**: Uses Share Tech Mono for the terminal look and Merriweather for documents.

Architecture Highlights
**Hybrid Rendering**: The game environment (game.php) is Server-Side Rendered (SSR) by PHP to set up the initial state, but the gameplay loop interacts with the server via AJAX/JSON (game_api.php) to validate moves securely.
**State Management**: Game state (strikes, time remaining, module status) is stored in the PHP $_SESSION and the MySQL database to prevent client-side cheating.
**Component-Based 3D**: You use A-Frame's component system (AFRAME.registerComponent) for features like the flashlight (flashlight-listener) and player movement (player-controller).

# Team Members and Contributions
Alviz - Programmer, Director
Arenas - Art design
Bagnas - Sound design
Bulan - Sound design
Dela Cruz - Programmer back end

# How to Play
1. **The Objective**
You must find and defuse a specific number of Modules (e.g., 2 for Easy, 3 for Hard) before the countdown timer reaches zero.
2. **Controls**
Move: W, A, S, D
Look: Mouse
Interact: E (Used for picking up items, placing them, cutting wires, or flipping switches)
Flashlight: L (Toggle on/off to see in the dark)
Pause: ESC or P
3. **Gameplay Loop**
Search: Navigate the dark facility to find Modules scattered on the floor. You can hold up to 2 modules in your inventory at a time.
Retrieve: Walk up to a module and press E to pick it up.
Deploy: Return to the starting desk. Look at the translucent blue slots marked "PLACE MOD" and press E to connect the module for defusal.
Investigate: Look at the walls for Clues.
Example: A riddle written on the wall might tell you which wire color to cut (e.g., "ENVY IS FATAL" implies Green).
Defuse: Interact with the module on the desk to solve the puzzle:
Wires: Cut the correct colored wire based on the wall riddles.
Switches: Toggle the switches to match a binary pattern found in the environment and press the submit button.
4. **Rules & Game Over**
Strikes: Incorrectly solving a puzzle (e.g., cutting the wrong wire) results in a Strike.
Game Over: You lose if you accumulate 3 Strikes or if the Timer runs out.

**Victory:** You win if all modules are successfully defused. Your score is calculated based on the difficulty and time remaining.

# How to Run the program
**Prerequisites**
   1. XAMPP
   2. php 8.0 or higher
   3. php libraries (PHPMailer, Google API)

**Installation steps**
   1. Prepare the files
      - locate your xampp installation folder
      - Open htdocs folder
      - Create a new folder named TheZeroProtocol
      - Paste all your projects files into C:\xampp\htdocs\TheZeroProtocol
   2. Install Dependencies
      - Open a terminal/command prompt inside your project folder
        (C:\xampp\htdocs\TheZeroProtocol
      - Run this command to install the required libraries: composter install
   3. Set up the database
      - Start apache and mysql in your xampp control panel
      - Open your browser and go to http://localhost/phpmyadmin
      - Click new and create a database named game
      - Run to browser: http://localhost/TheZeroProtocol/setup_tables.php
        
**Start the game locally**
   1. Open xampp, start apache and mysql
   2. Launch to browser: http://localhost/TheZeroProtocol

# OOP Implementation

**1. Encapsulation**
Concept: Encapsulation protects the game state from being accidentally broken or cheated. It bundles data (like health or scores) with the methods that modify them, keeping the internal details hidden.

File Used: classes/Player.php

How it applies:

The Object: The Player class holds the private data $mistakes and $isAlive.

The Protection: You cannot directly write $player->mistakes = 0. You are forced to use the method $player->addMistake(), which ensures the "3 Strikes" rule is checked every single time.

**2. Inheritance**
Concept: Inheritance allows specific game objects to share common traits from a "Parent" definition, reducing repetition.

Files Used: classes/modules/WireModule.php & classes/modules/KeypadModule.php

How it applies:

The Children: Your WireModule.php and KeypadModule.php are the specific implementations.

The Shared Traits: Both files share the same structure: they both have an $id, an $isDefused status, and a solvePuzzle() method. In a strict OOP setup, these would extend a common parent class to avoid writing that shared code twice.

**3. Polymorphism**
Concept: Polymorphism allows the game system to treat different objects as if they were the same, even though they behave differently.

File Used: classes/GameSession.php

How it applies:

The Manager: The GameSession.php script loads a list of mixed modules.

The Action: It calls $module->solvePuzzle($answer) on them. It does not check if the module is a Wire or a Keypad first. It relies on the fact that both files have a function with that exact name, allowing the game to run smoothly regardless of what puzzle type is loaded.

**4. Abstraction**
Concept: Abstraction hides complex details and only shows the essential "interface" to the rest of the system.

File Used: classes/modules/WireModule.php

How it applies:

Hiding Complexity: The WireModule.php file contains logic about "cutting" specific colors and checking solutions.

The Interface: The main game doesn't need to know about wire colors. It only uses the "abstract" concept of "Solving." The detailed code for how it is solved is hidden away inside this specific file, keeping the main game logic clean.

# Source Code
https://drive.google.com/drive/folders/1u27Byjk-q1Ngv9fDwBnGsJTR3u55gAQI?usp=sharing

# Video Demonstration
- Alviz, Arcel: 
- Arenas, Juaquin Alejandro:
- Bagnas, Ardy:
- Bulan, Christian Jacob:
- Dela Cruz, Kenji:



   

