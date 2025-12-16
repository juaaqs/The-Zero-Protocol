<?php
// game_api.php
header('Content-Type: application/json');
session_start();
require_once 'db_config.php';
require_once 'classes/GameSession.php';

// Check Auth
if (!isset($_SESSION['user_id']) || !isset($_SESSION['game_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get JSON Input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$moduleId = $input['moduleId'];
$moduleType = $input['moduleType'];
$answer = $input['answer'];

$game = new GameSession($pdo);
if (!$game->loadGame($_SESSION['game_id'])) {
    echo json_encode(['error' => 'Game load error']);
    exit;
}

// Process the answer logic
// NOTE: actual logic depends on your GameSession class methods.
// Assuming submitModuleAnswer returns an array with:
// 'solved' => bool, 'strikes' => int, 'game_status' => 'active'|'win'|'lose_strike'

$result = $game->submitModuleAnswer($moduleId, $answer);

$response = [
    'solveResult' => [
        'solved' => $result['solved']
    ],
    'strikes' => $result['strikes'],
    'status' => $result['game_status']
];

echo json_encode($response);
exit;
?>