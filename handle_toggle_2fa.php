<?php
// handle_toggle_2fa.php
// Handles the AJAX request to enable or disable 2FA for the logged-in user.

session_start();
require 'db_config.php';
require 'email_helper.php'; // Required if we want to send a confirmation email

// Set the response header to JSON, as the frontend expects it.
header('Content-Type: application/json');

// Check for required session data
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit();
}

// Check for required input data
$status = $_POST['status'] ?? null;
if (!in_array($status, ['enable', 'disable'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action provided.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$is_enabling = ($status === 'enable');
// Convert status to 1 (true) or 0 (false) for the database
$db_value = $is_enabling ? 1 : 0;
$log_action = $is_enabling ? 'Enabled' : 'Disabled';

try {
    // 1. Fetch user email (needed for the confirmation email)
    $stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_email = $user_data['email'] ?? null;

    // 2. Prepare and execute the query to update the users table
    // ASSUMPTION: Your users table has a column named 'is_2fa_enabled'
    $sql = "UPDATE users SET is_2fa_enabled = ? WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$db_value, $user_id]);

    // 3. Send a confirmation email (highly recommended for security)
    if ($user_email) {
        $subject = "The Zero Protocol: 2FA {$log_action}";
        $body = "
            <div style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2>SECURITY ALERT</h2>
                <p>Your Two-Factor Authentication (2FA) has been <strong>{$log_action}</strong> on The Zero Protocol access terminal.</p>
                <p>If you did not perform this action, please change your passcode immediately.</p>
            </div>
        ";
        sendVerificationEmail($user_email, $subject, $body);
    }

    // 4. Send success response back to the frontend
    echo json_encode([
        'status' => 'success',
        'message' => "Two-Factor Access has been {$log_action}.",
        'new_status' => $log_action
    ]);
    exit();

} catch (PDOException $e) {
    // Log the error for internal debugging
    error_log("2FA Toggle DB Error: " . $e->getMessage());
    
    // Send a generic error response back to the frontend
    echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
    exit();
}
?>