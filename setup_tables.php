<?php
// setup_tables.php
require 'db_config.php';

try {
    echo "<h1>Resetting Game Database...</h1>";

    // 1. DISABLE CHECKS (Allows dropping tables with relationships)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 2. DROP OLD TABLES
    $pdo->exec("DROP TABLE IF EXISTS game_modules");
    $pdo->exec("DROP TABLE IF EXISTS game_sessions");
    
    echo "<p style='color: orange'>&#9888; Old tables dropped.</p>";

    // 3. RE-ENABLE CHECKS
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // 4. CREATE game_sessions TABLE
    // Added: 'strikes' and 'score' columns
    $sql1 = "CREATE TABLE game_sessions (
        game_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        start_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        time_limit INT NOT NULL,
        difficulty VARCHAR(50),
        is_completed TINYINT(1) DEFAULT 0,
        strikes INT DEFAULT 0,
        score INT DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    
    $pdo->exec($sql1);
    echo "<p style='color: green'>&#10004; Table 'game_sessions' created.</p>";

    // 5. CREATE game_modules TABLE
    $sql2 = "CREATE TABLE game_modules (
        module_id INT AUTO_INCREMENT PRIMARY KEY,
        game_id INT NOT NULL,
        module_type VARCHAR(50) NOT NULL,
        is_defused TINYINT(1) DEFAULT 0,
        solution VARCHAR(255),
        FOREIGN KEY (game_id) REFERENCES game_sessions(game_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";

    $pdo->exec($sql2);
    echo "<p style='color: green'>&#10004; Table 'game_modules' created.</p>";

    echo "<h3>Database Ready! <a href='menu.php'>Return to Menu</a></h3>";

} catch (PDOException $e) {
    echo "<h3 style='color: red'>Error: " . $e->getMessage() . "</h3>";
}
?>