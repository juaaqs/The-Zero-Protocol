<?php
// handle_add_password.php
// Processes the form submission for adding a password to a Google account.

session_start();
require_once 'db_config.php'; // Database connection

// 1. --- SESSION PROTECTION & CHECK ---
// Must be logged in (not guest)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0 || (isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true)) {
    header("Location: login.php");
    exit;
}

// Ensure form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['new_password']) || !isset($_POST['confirm_password'])) {
    header("Location: add_password.php?error=Invalid request.");
    exit;
}

$user_id = $_SESSION['user_id'];
$new_password = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

try {
    // 2. --- CHECK IF PASSWORD ALREADY EXISTS (Again for security) ---
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) { // User somehow doesn't exist anymore
        throw new Exception("User account not found.");
    }
    if (!empty($user['password_hash'])) {
        header("Location: account_settings.php?error=Password login already enabled.");
        exit;
    }

    // 3. --- VALIDATE NEW PASSWORD ---
    if ($new_password !== $confirm_password) {
        header("Location: add_password.php?error=Passwords do not match.");
        exit;
    }
    if (strlen($new_password) < 8) {
        header("Location: add_password.php?error=Password must be at least 8 characters.");
        exit;
    }

    // 4. --- HASH & UPDATE PASSWORD ---
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $updateStmt->execute([$password_hash, $user_id]);

    // 5. --- REDIRECT WITH SUCCESS ---
    header("Location: account_settings.php?success=Password login added successfully!");
    exit;

} catch (PDOException $e) {
    error_log("Add Password DB Error: " . $e->getMessage());
    header("Location: add_password.php?error=A database error occurred.");
    exit;
} catch (Exception $e) {
    error_log("Add Password General Error: " . $e->getMessage());
    header("Location: add_password.php?error=An unexpected error occurred.");
    exit;
}

?>