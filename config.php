<?php
// config.php
// Stores our application's secret keys and constants.

// IMPORTANT:
// 1. Paste your keys here.
// 2. DO NOT share this file with anyone.
// 3. If you use Git/GitHub, add this file to your .gitignore

// --- Google API Keys ---
// Paste the keys you got from the Google Cloud Console
define('GOOGLE_CLIENT_ID', '888028912303-2k9kk4uejp4805ropiqgtovuh1ijeig3.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');

// --- Application URLs ---
// This is the full URL to your project's root
define('BASE_URL', 'http://localhost/TheZeroProtocol');

define('GOOGLE_REDIRECT_URL', BASE_URL . '/handle_google_login.php');

// --- *** GMAIL SMTP Configuration (for sending 2FA codes) *** ---

define('SMTP_HOST', 'smtp.gmail.com');      // Official Gmail SMTP server
define('SMTP_PORT', 587);                   // Port for TLS (required by Gmail)

define('SMTP_USERNAME', 'thezeroprotocol.game@gmail.com'); 

define('SMTP_PASSWORD', 'yqio mwwq edkv hdew'); 

// Set the professional 'From' name for security emails
define('SMTP_FROM_EMAIL', 'security@thezeroprotocol.com'); 
define('SMTP_FROM_NAME', 'The Zero Protocol Security');   
// --- *** END OF GMAIL CONFIGURATION *** ---

?>