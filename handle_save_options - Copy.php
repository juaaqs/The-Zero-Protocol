<?php
// handle_save_options.php
// Saves the user's system preferences (like audio volume) to a cookie.

session_start();

// 1. Get the volume value from the form (0 to 100)
$volume_percent = $_POST['volume'] ?? 30; // Default to 30% if not set

// Ensure the volume is a valid integer between 0 and 100
$volume_percent = (int) $volume_percent;
$volume_percent = max(0, min(100, $volume_percent));

// 2. Set the cookie
// Cookie Name: 'game_volume'
// Value: The percentage (0-100)
// Expiration: Set for 30 days (time() + seconds in 30 days)
$cookie_name = 'game_volume';
$cookie_value = $volume_percent;
$expiration_time = time() + (86400 * 30); // 86400 seconds = 1 day

// Set the cookie globally across the entire site path
setcookie($cookie_name, $cookie_value, $expiration_time, '/');

// 3. Set a session message for feedback
$_SESSION['message'] = ['type' => 'success', 'text' => 'System configuration saved successfully.'];

// 4. Redirect the user back to the options page
header("Location: options.php");
exit();
?>