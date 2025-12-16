<?php
// handle_guest.php
// Logs the user in as a "Guest".

// We must start the session to store the guest's data
session_start();

// Set up the guest session
$_SESSION['user_id'] = 0; // 0 is a good ID for a "non-user" or guest
$_SESSION['display_name'] = "Guest";
$_SESSION['avatar_url'] = "assets/images/avatar-default.png"; // A default avatar
$_SESSION['is_guest'] = true; // This is the important flag to check later

// Send them to the main menu
header("Location: menu.php");
exit;
?>