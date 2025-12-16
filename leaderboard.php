<?php
// leaderboard.php
session_start();
require 'db_config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$isGuest = $_SESSION['is_guest'] ?? false;

// Default difficulty
$difficulty = $_GET['difficulty'] ?? 'easy';
$valid_difficulties = ['easy', 'medium', 'hard'];
if (!in_array($difficulty, $valid_difficulties)) {
    $difficulty = 'easy';
}

$rankings = [];
try {
    // UPDATED QUERY: Uses 'game_sessions' table
    $sql = "
        SELECT 
            u.display_name, 
            g.score, 
            u.user_id,
            u.avatar_url,
            g.start_time
        FROM game_sessions g
        JOIN users u ON g.user_id = u.user_id
        WHERE g.difficulty = ? AND g.is_completed = 1
        ORDER BY g.score DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$difficulty]);
    $rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $rankings = [];
    error_log("Leaderboard Error: " . $e->getMessage());
}

// --- My Rank Feature ---
$my_rank = "N/A";
$my_best_score = "N/A";

if (!$isGuest) {
    try {
        $stmt_my = $pdo->prepare("SELECT MAX(score) as best_score FROM game_sessions WHERE user_id = ? AND difficulty = ? AND is_completed = 1");
        $stmt_my->execute([$current_user_id, $difficulty]);
        $my_data = $stmt_my->fetch(PDO::FETCH_ASSOC);

        if ($my_data && $my_data['best_score']) {
            $my_best_score = number_format($my_data['best_score']);
            
            $stmt_rank = $pdo->prepare("SELECT COUNT(*) as rank FROM game_sessions WHERE difficulty = ? AND is_completed = 1 AND score > ?");
            $stmt_rank->execute([$difficulty, $my_data['best_score']]);
            $rank_data = $stmt_rank->fetch(PDO::FETCH_ASSOC);
            
            $my_rank = $rank_data['rank'] + 1;
        }
    } catch (PDOException $e) { /* Ignore */ }
}

function formatTime($seconds) {
    if (!is_numeric($seconds)) return "N/A";
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf("%02d:%02d", $minutes, $seconds);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Zero Protocol - Agent Rankings</title>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Special+Elite&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/3833be9c2c.js" crossorigin="anonymous"></script>
    <style>
        body { background-color: #000; color: #f5eeda; font-family: 'Merriweather', serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .leaderboard-panel { width: 90%; max-width: 800px; background-color: #f5eeda; border: 5px double #333; padding: 40px; box-shadow: 10px 10px 25px rgba(0,0,0,0.5); color: #333; position: relative; z-index: 5; }
        .report-title { font-family: 'Special Elite', monospace; font-size: 3em; color: #2F2F2F; text-align: center; margin: 0 0 10px 0; text-transform: uppercase; }
        .report-subtitle { font-family: 'Merriweather', serif; font-size: 1.1em; color: #b71c1c; text-align: center; margin-bottom: 25px; border-bottom: 1px dashed #aaa; padding-bottom: 15px; }
        
        .leaderboard-tabs { display: flex; justify-content: center; margin-bottom: 25px; gap: 10px; }
        .tab-btn { font-family: 'Special Elite', monospace; font-size: 1.2em; color: #f5eeda; text-decoration: none; padding: 10px 20px; border: 2px solid #333; background-color: #333; transition: 0.2s; }
        .tab-btn:hover { background-color: #555; }
        .tab-btn.active { background-color: #b71c1c; border-color: #b71c1c; }

        .ranking-table { width: 100%; border-collapse: collapse; font-family: 'Merriweather', serif; }
        .ranking-table th { font-family: 'Special Elite', monospace; background-color: #333; color: #f5eeda; padding: 12px 10px; text-align: left; text-transform: uppercase; }
        .ranking-table td { padding: 12px 10px; border-bottom: 1px solid #ccc; color: #2F2F2F; vertical-align: middle; }
        
        .agent-cell { display: flex; align-items: center; }
        .agent-avatar-small { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #333; margin-right: 15px; object-fit: cover; }
        
        .top-agent { background-color: rgba(218, 165, 32, 0.2); border-left: 4px solid gold; }
        .current-user { background-color: rgba(183, 28, 28, 0.1); font-weight: bold; }

        .btn-back { position: absolute; top: 30px; left: 30px; font-family: 'Special Elite', monospace; font-size: 1.2em; color: #f5eeda; text-decoration: none; padding: 5px 10px; border: 1px solid #f5eeda; background-color: rgba(0,0,0,0.5); }
        .btn-back:hover { background-color: #333; color: #fff; }
        
        .fullscreen-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; }
        .video-background { position: fixed; top: 50%; left: 50%; min-width: 100%; min-height: 100%; width: auto; height: auto; transform: translateX(-50%) translateY(-50%); z-index: 1; pointer-events: none; }
        #bg-video { width: 100vw; height: 56.25vw; min-height: 100vh; min-width: 177.77vh; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        .video-background::after { content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.65); z-index: 2; }
        .film-grain-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-image: url('assets/images/noise-overlay.png'); animation: grain 0.4s steps(1) infinite; z-index: 3; pointer-events: none; opacity: 0.15; }
        @keyframes grain { 0% { transform: translate(0, 0); } 10% { transform: translate(-1px, -1px); } 100% { transform: translate(1px, -1px); } }
    </style>
</head>
<body>

    <div class="fullscreen-container">
        <div class="video-background">
            <video autoplay muted loop playsinline id="bg-video">
                <source src="assets/video/bg-video.mp4" type="video/mp4">
            </video>
        </div>
        <div class="film-grain-overlay"></div>
        
        <a href="menu.php" class="btn-back">&larr; Return to Hub</a>

        <div class="leaderboard-panel">
            <h1 class="report-title">Field Operative Ranking</h1>
            <p class="report-subtitle">CLASSIFIED DATA // TOP 10 HIGH SCORES</p>
            
            <div class="leaderboard-tabs">
                <a href="leaderboard.php?difficulty=easy" class="tab-btn <?php echo ($difficulty == 'easy') ? 'active' : ''; ?>">Easy</a>
                <a href="leaderboard.php?difficulty=medium" class="tab-btn <?php echo ($difficulty == 'medium') ? 'active' : ''; ?>">Medium</a>
                <a href="leaderboard.php?difficulty=hard" class="tab-btn <?php echo ($difficulty == 'hard') ? 'active' : ''; ?>">Hard</a>
            </div>

            <?php if (!$isGuest): ?>
            <div style="text-align: center; margin-bottom: 20px; font-family: 'Special Elite', monospace; color: #b71c1c;">
                YOUR RANK: <strong>#<?php echo $my_rank; ?></strong> | BEST SCORE: <strong><?php echo $my_best_score; ?></strong>
            </div>
            <?php endif; ?>

            <table class="ranking-table">
                <thead>
                    <tr>
                        <th width="10%">#</th>
                        <th>Agent Callsign</th>
                        <th width="20%">Score</th>
                        <th width="20%">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rankings)): ?>
                        <tr><td colspan="4" style="text-align:center;">No mission data available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rankings as $index => $row): 
                            $row_class = ($row['user_id'] == $current_user_id) ? 'current-user' : '';
                            if ($index == 0) $row_class .= ' top-agent';
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td style="text-align:center; font-weight:bold;"><?php echo $index + 1; ?></td>
                            <td>
                                <div class="agent-cell">
                                    <img src="<?php echo htmlspecialchars($row['avatar_url'] ?? 'assets/images/avatar-default.png'); ?>" class="agent-avatar-small">
                                    <?php echo htmlspecialchars($row['display_name']); ?>
                                </div>
                            </td>
                            <td style="font-weight:bold; color:#b71c1c;"><?php echo number_format($row['score']); ?></td>
                            <td style="font-size:0.9em;"><?php echo date('M d, Y', strtotime($row['start_time'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>