<?php
// handle_delete_account.php
// This script securely deletes the user's account and all associated data.

session_start();
require 'db_config.php'; 

// 1. Check for required session data
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$password_confirmation = $_POST['password'] ?? '';

// Define a helper function to set messages and redirect
function setMessageAndRedirect($type, $text, $location = "initiate_delete.php") {
    $_SESSION['message'] = ['type' => $type, 'text' => $text];
    header("Location: " . $location);
    exit();
}

if (empty($password_confirmation)) {
    setMessageAndRedirect('error', 'Passcode confirmation is required to proceed.');
}

try {
    // 2. Retrieve the user's current password hash
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User not found (shouldn't happen, but good check)
        setMessageAndRedirect('error', 'User record not found. Please log back in.', 'logout.php');
    }

    // 3. Verify the passcode one last time
    if (!password_verify($password_confirmation, $user['password_hash'])) {
        setMessageAndRedirect('error', 'Incorrect passcode. Self-Destruct sequence aborted.');
    }

    // 4. --- EXECUTE SELF-DESTRUCT SEQUENCE ---
    // The key here is the "ON DELETE CASCADE" feature on your Foreign Keys.
    // Deleting the user row should automatically delete all related data (scores, game_sessions, etc.)

    $delete_stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $delete_stmt->execute([$user_id]);

    // 5. Success: Destroy the session and log out
    session_destroy();
    
    // Set a success message to display on the login page
    $_SESSION['message'] = [
        'type' => 'success', 
        'text' => "Agent file ({$username}) successfully purged. All data destroyed."
    ];
    
    // Redirect to the login page
    header("Location: login.php");
    exit();

} catch (PDOException $e) {
    error_log("Account Deletion DB Error: " . $e->getMessage());
    setMessageAndRedirect('error', 'A critical error prevented file destruction. Contact Mission Control.', 'profile.php');
}
?>