<?php
// handle_change_password.php
// Handles the request to change the user's password from the profile screen.

session_start();
require 'db_config.php'; 

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Send them back to login if session is invalid
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

// Define a helper function to set messages and redirect
function setMessageAndRedirect($type, $text) {
    $_SESSION['message'] = ['type' => $type, 'text' => $text];
    header("Location: profile.php");
    exit();
}

// 1. Validation Checks
if (empty($current_password) || empty($new_password)) {
    setMessageAndRedirect('error', 'All passcode fields must be filled.');
}

if (strlen($new_password) < 8) {
    setMessageAndRedirect('error', 'New passcode must be at least 8 characters long.');
}

if ($current_password === $new_password) {
    setMessageAndRedirect('error', 'New passcode cannot be the same as the current one.');
}

try {
    // 2. Retrieve the user's current password hash from the database
    // ASSUMPTION: Using 'password_hash' column
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Should not happen if user_id is in session, but a safe check
        setMessageAndRedirect('error', 'User record not found. Please re-login.');
    }

    // 3. Verify the CURRENT password
    if (!password_verify($current_password, $user['password_hash'])) {
        setMessageAndRedirect('error', 'Current passcode is incorrect. Access denied.');
    }

    // 4. Hash the new password securely
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    // 5. Update the user's password_hash in the database
    $update_stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $update_stmt->execute([$hashed_new_password, $user_id]);

    // 6. Success!
    setMessageAndRedirect('success', 'Passcode updated successfully. Your agent file is secured.');

} catch (PDOException $e) {
    error_log("Password Change DB Error: " . $e->getMessage());
    setMessageAndRedirect('error', 'A database error occurred. Could not update passcode.');

}
?>