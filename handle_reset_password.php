<?php
// handle_reset_password.php
// This script verifies the secure token and updates the user's password.

session_start();
require 'db_config.php'; 

// 1. Check for required session data (set in reset_password.php)
if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_token'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Session expired. Please request a new link.'];
    header("Location: forgot_password.php");
    exit();
}

// 2. Get data from the form and session
$user_id = $_SESSION['reset_user_id'];
$token = $_SESSION['reset_token'];
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// 3. Validation
if (empty($new_password) || empty($confirm_password)) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Passcode fields cannot be empty.'];
    header("Location: reset_password.php?token={$token}");
    exit();
}

if ($new_password !== $confirm_password) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Passcodes do not match.'];
    header("Location: reset_password.php?token={$token}");
    exit();
}

// Optional: Add password strength checks here (e.g., min length of 8)
if (strlen($new_password) < 8) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Passcode must be at least 8 characters long.'];
    header("Location: reset_password.php?token={$token}");
    exit();
}

try {
    // 4. Re-verify the token and check for expiration one last time
    $stmt = $pdo->prepare("SELECT * FROM verification_codes WHERE user_id = ? AND code = ? AND expires_at > NOW()");
    $stmt->execute([$user_id, $token]);
    $reset_record = $stmt->fetch();

    if (!$reset_record) {
        // Token has been used, deleted, or expired in the time since the page loaded.
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Secure token is no longer valid. Please request a new link.'];
        header("Location: forgot_password.php");
        // We delete the token just in case it was only expired
        $pdo->prepare("DELETE FROM verification_codes WHERE user_id = ?")->execute([$user_id]);
        exit();
    }

    // 5. Hash the new password and update the user's account
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // ASSUMPTION: Updating the 'password_hash' column
    $update_stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $update_stmt->execute([$hashed_password, $user_id]);

    // 6. Delete the token from the database (prevents replay attacks)
    $pdo->prepare("DELETE FROM verification_codes WHERE user_id = ?")->execute([$user_id]);

    // 7. Clean up temporary session variables
    unset($_SESSION['reset_user_id']);
    unset($_SESSION['reset_token']);
    
    // 8. Success! Send the user to login with a success message.
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Passcode successfully reset. Access terminal now.'];
    header("Location: login.php");
    exit();

} catch (PDOException $e) {
    error_log("Password Reset Error: " . $e->getMessage());
    $_SESSION['message'] = ['type' => 'error', 'text' => 'A critical system error occurred during reset.'];
    header("Location: login.php");
    exit();
}
?>