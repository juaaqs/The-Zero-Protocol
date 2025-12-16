<?php
// handle_start_game.php

// --- DEBUGGING: ENABLE ERRORS ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_config.php'; 

// Check if the file actually exists before requiring it
if (!file_exists('classes/GameSession.php')) {
    die("Error: classes/GameSession.php not found. Please ensure the file is in the 'classes' folder.");
}
require 'classes/GameSession.php';

// 1. Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$difficulty = $_GET['difficulty'] ?? 'easy'; 

try {
    $gameSession = new GameSession($pdo);

    $new_game_id = $gameSession->startNewGame($user_id, $difficulty);

    if ($new_game_id) {
        $_SESSION['game_id'] = $new_game_id;
        header("Location: game.php");
        exit();
    } else {
        die("Error: Could not create game session (Database returned false).");
    }

} catch (Exception $e) {
    die("Critical Error: " . $e->getMessage());
}
?>