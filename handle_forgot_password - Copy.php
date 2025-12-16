<?php
// handle_forgot_password.php
// Generates a unique secure token and sends a password reset link to the agent's email.

session_start();
require 'db_config.php';
require 'config.php'; // Needed for BASE_URL constant
require 'email_helper.php'; 

// 1. Get the email address from the form
$email = $_POST['email'] ?? '';

if (empty($email)) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Email address is required.'];
    header("Location: forgot_password.php");
    exit();
}

try {
    // 2. Check if the user exists
    $stmt = $pdo->prepare("SELECT user_id, display_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // IMPORTANT: We send a generic success message even if the user doesn't exist
        // This is a security measure to prevent revealing valid email addresses to attackers.
        $_SESSION['message'] = ['type' => 'success', 'text' => 'If this email is in our file, a secure reset link has been sent. Check your inbox.'];
        header("Location: forgot_password.php");
        exit();
    }
    
    $user_id = $user['user_id'];
    $display_name = $user['display_name'];

    // 3. Generate a secure, unique, time-limited token
    // We use bin2hex(random_bytes(32)) for a very long, secure token (64 characters)
    $token = bin2hex(random_bytes(32)); 
    $currentDateTime = date("Y-m-d H:i:s");
    $expirationDateTime = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token expires in 1 hour
    
    // 4. Store the token in the database
    // We reuse the verification_codes table, storing the long token string
    $sql = "INSERT INTO verification_codes (user_id, code, created_at, expires_at)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE code = VALUES(code), created_at = VALUES(created_at), expires_at = VALUES(expires_at)";

    // NOTE: This SQL assumes you ADDED an 'expires_at' column to your verification_codes table.
    // If not, use the code below instead:
    /* $sql = "INSERT INTO verification_codes (user_id, code, created_at)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE code = VALUES(code), created_at = VALUES(created_at)";
    */

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $token, $currentDateTime, $expirationDateTime]);

    // 5. Construct the reset link
    $reset_link = BASE_URL . '/reset_password.php?token=' . $token;

    // 6. Prepare and send the email
    $subject = "The Zero Protocol: Passcode Reset Request";
    $body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd;'>
            <h2>CLASSIFIED ACCESS: Passcode Reset</h2>
            <p>Agent {$display_name},</p>
            <p>We received a request to reset the passcode for your agent file ({$email}).</p>
            <p>To proceed, click the secure link below:</p>
            <p style='text-align: center; margin: 25px 0;'>
                <a href='{$reset_link}' style='display: inline-block; padding: 10px 20px; background-color: #333; color: #f5eeda; text-decoration: none; font-weight: bold;'>
                    RESET PASSCODE
                </a>
            </p>
            <p style='color: #b71c1c;'><strong>WARNING:</strong> This link is only valid for 1 hour. If you did not request this, you may ignore this email.</p>
            <p>— The Zero Protocol Security Team</p>
        </div>
    ";

    $emailSent = sendVerificationEmail($email, $subject, $body);

    if ($emailSent) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'A secure link has been sent to your email.'];
    } else {
        // If email failed to send, log the error but still tell the user to check their email (safer UX)
        $_SESSION['message'] = ['type' => 'error', 'text' => 'There was an error sending the email. Please try again.'];
    }

    // Redirect back to the request form
    header("Location: forgot_password.php");
    exit();

} catch (Exception $e) {
    error_log("Forgot Password Failure: " . $e->getMessage());
    $_SESSION['message'] = ['type' => 'error', 'text' => 'A critical system error occurred. Please try again later.'];
    header("Location: forgot_password.php");
    exit();
}
?>