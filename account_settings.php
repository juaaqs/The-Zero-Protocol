<?php
// account_settings.php (with Tabs)

session_start();
require_once 'db_config.php'; // Database connection

// 1. --- SESSION PROTECTION ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0 || (isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true)) {
    header("Location: login.php?error=Please log in to access account settings.");
    exit;
}

// 2. --- GET USER DATA (FROM DATABASE) ---
$user_id = $_SESSION['user_id'];
$user = null;
$error = null;
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : null; // Get success message

try {
    // Fetch user details including 2FA status
    $stmt = $pdo->prepare("SELECT email, display_name, password_hash, google_id, avatar_url, is_2fa_enabled FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) { throw new Exception("User not found in database."); }
} catch (Exception $e) {
    error_log("Account Settings Load Error: " . $e->getMessage());
    $error = "Could not load account details.";
}

// Determine login methods available
$hasPasswordAuth = !empty($user['password_hash']);
$hasGoogleAuth = !empty($user['google_id']);
$is_2fa_enabled = $user['is_2fa_enabled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Defuse It!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Poppins:wght@400;700&display.swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/3833be9c2c.js" crossorigin="anonymous"></script>

    <style>
        /* --- DARK & AMBER THEME --- */
        :root { /* ... Color definitions ... */
            --color-amber: #f1c40f; --color-amber-dark: #e0b40e; --color-amber-glow: rgba(241, 196, 15, 0.4);
            --color-red-danger: #e74c3c; --color-red-danger-dark: #c0392b;
        }

        body.tech-bg { /* ... Body styles ... */
            background-image: url('assets/images/tech-hub-bg.jpg'); background-size: cover; background-position: center;
            background-attachment: fixed; background-color: #111; font-family: 'Share Tech Mono', monospace;
            color: #ffffff; overflow: hidden;
        }

        .menu-container { /* ... Container styles ... */
            background-color: #181818; border: 2px solid var(--color-amber); box-shadow: 0 0 20px var(--color-amber-glow);
            padding: 0; /* No padding for tabs */ border-radius: 8px; width: 90%; max-width: 700px;
            margin: 10vh auto 0; text-align: center;
        }

        .menu-container h1 { /* ... H1 styles ... */
             color: var(--color-amber); font-size: 2.5em; margin: 30px 40px 25px;
             text-transform: uppercase; padding-bottom: 20px; border-bottom: 1px solid var(--color-amber);
        }

        /* --- Tab Navigation --- */
        .options-tabs { /* Using same class name as options.php */
            display: flex; background-color: rgba(0,0,0,0.3); border-bottom: 2px solid var(--color-amber);
        }
        .tab-link { /* ... Tab link styles ... */
            flex-grow: 1; padding: 15px 10px; cursor: pointer; text-align: center; font-size: 1.1em;
            color: #aaa; text-transform: uppercase; border-bottom: 4px solid transparent; transition: all 0.2s ease;
        }
        .tab-link:hover { color: #fff; background-color: rgba(255, 255, 255, 0.05); }
        .tab-link.active { color: var(--color-amber); border-bottom-color: var(--color-amber); background-color: rgba(0,0,0,0.2); }

        /* --- Tab Content Area --- */
        .options-content { /* Using same class name as options.php */
            padding: 30px 40px; /* Padding for content */
            /* Removed max-height, let content define height */
            /* overflow-y: auto; */
            text-align: left; /* Align content left */
        }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* --- Styling for options within panels --- */
        .options-content h2 {
             color: var(--color-amber); font-size: 1.5em; margin-top: 0; margin-bottom: 20px;
             border-bottom: 1px solid var(--color-amber); padding-bottom: 5px; text-align: left;
        }
         .options-content p {
            font-size: 1.1em; color: #f1f1f1; margin-bottom: 20px; text-align: left;
        }

        /* Profile Display */
        .user-profile { /* ... Profile styles ... */
             display: flex; align-items: center; justify-content: flex-start; margin-bottom: 20px;
             background: rgba(0, 0, 0, 0.3); padding: 15px; border-radius: 5px; border-left: 3px solid var(--color-amber);
        }
        .user-profile img { width: 50px; height: 50px; border-radius: 50%; border: 2px solid var(--color-amber); margin-right: 20px; }
        .user-profile div { text-align: left; }
        .user-profile h3 { margin: 0; color: #fff; font-size: 1.4em; }
        .user-profile h3 span { color: var(--color-amber); font-size: 1.1em; }
        .user-profile p { margin: 0; color: #aaa; font-size: 1.0em; }

        /* --- Buttons --- */
        .btn-tech-amber { /* ... Amber button styles ... */
            background: var(--color-amber); border: 2px solid var(--color-amber); color: #111; font-family: 'Share Tech Mono', monospace;
            font-size: 1.2em; padding: 10px 20px; border-radius: 5px; text-transform: uppercase; text-decoration: none;
            display: inline-block; margin: 10px 5px 5px 0; cursor: pointer; box-shadow: 0 0 15px var(--color-amber-glow);
            transition: all 0.2s ease;
        }
        .btn-tech-amber:hover { background: var(--color-amber-dark); border-color: var(--color-amber-dark); color: #000; }
        .btn-tech-danger-solid { /* ... Danger button styles ... */
            background: var(--color-red-danger); border: 2px solid var(--color-red-danger); color: #fff; font-family: 'Share Tech Mono', monospace;
            font-size: 1.2em; padding: 10px 20px; border-radius: 5px; text-transform: uppercase; text-decoration: none;
            display: inline-block; margin: 10px 5px 5px 0; cursor: pointer; transition: all 0.2s ease;
        }
        .btn-tech-danger-solid:hover { background: var(--color-red-danger-dark); border-color: var(--color-red-danger-dark); }

        /* Back Link */
        .bottom-link { /* ... Back link styles ... */
            margin-top: 30px; text-align: center; padding-bottom: 30px;
        }
        .bottom-link a { color: var(--color-amber); text-decoration: none; font-size: 1.1em; }
        .bottom-link a:hover { text-decoration: underline; }
    </style>
</head>
<body class="tech-bg">

    <div class="menu-container">

        <div class="options-tabs">
            <div class="tab-link active" data-tab="profile">Profile</div>
            <div class="tab-link" data-tab="security">Security</div>
            <div class="tab-link" data-tab="account">Account</div>
        </div>

        <div class="options-content">
            <h1>Account Settings</h1>

            <?php if ($error): ?>
                <p class="error-notice"><?php echo $error; ?></p>
            <?php elseif ($success): ?>
                <p class="score-saved-notice"><?php echo $success; ?></p>
            <?php endif; ?>

            <?php if ($user): ?>
                <div id="tab-profile" class="tab-panel active">
                    <h2>Your Profile</h2>
                    <div class="user-profile">
                        <img src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'assets/images/avatar-default.png'); ?>" alt="Player Avatar">
                        <div>
                            <h3><span><?php echo htmlspecialchars($user['display_name']); ?></span></h3>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                    <a href="profile.php" class="btn-tech-amber">[ View Full Player Stats ]</a>
                    <p style="font-size: 0.9em; color: #aaa; margin-top: 15px;">Avatar can be changed on the Full Player Stats page.</p>
                </div>

                <div id="tab-security" class="tab-panel">
                    <h2>Login Security</h2>

                    <h3>Password Login</h3>
                    <?php if ($hasPasswordAuth): ?>
                        <p>Password login is enabled.</p>
                         <a href="change_password.php" class="btn-tech-amber">[ Change Password ]</a>
                    <?php else: ?>
                        <p>You currently sign in using Google. You can add a password for an alternative login method.</p>
                        <a href="add_password.php" class="btn-tech-amber">[ Add Password Login ]</a>
                    <?php endif; ?>

                    <?php if ($hasPasswordAuth): ?>
                        <h3 style="margin-top: 30px;">2-Factor Authentication (2FA)</h3>
                        <form action="handle_toggle_2fa.php" method="POST" style="margin: 0;">
                            <?php if ($is_2fa_enabled): ?>
                                <p>2FA is currently <strong style="color: #2ecc71;">ENABLED</strong>. Logins will require an email code.</p>
                                <button type="submit" class="btn-tech-danger-solid">[ Disable 2FA ]</button>
                            <?php else: ?>
                                <p>2FA is currently <strong style="color: var(--color-red-danger);">DISABLED</strong>. Logins only require your password.</p>
                                <button type="submit" class="btn-tech-amber">[ Enable 2FA ]</button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>

                    <h3 style="margin-top: 30px;">Google Login</h3>
                    <?php if ($hasGoogleAuth): ?>
                        <p>Your account is linked to Google.</p>
                        <?php else: ?>
                        <p>You can link your Google account for easier login.</p>
                        <a href="google-login.php?action=link" class="btn-tech-amber">[ Link Google Account ]</a>
                    <?php endif; ?>
                </div>

                <div id="tab-account" class="tab-panel">
                    <h2>Account Actions</h2>
                    <p style="color: var(--color-red-danger);">Warning: Deleting your account is permanent and cannot be undone.</p>
                    <a href="initiate_delete.php" class="btn-tech-danger-solid">[ Initiate Account Deletion ]</a>
                    </div>

            <?php endif; ?>

            <div class="bottom-link">
                <a href="menu.php">&larr; Back to Main Menu</a>
            </div>

        </div> </div> <script src="assets/js/main.js"></script>
    <script>
        // --- Tab Switching Logic (Copied from options.php) ---
        document.addEventListener('DOMContentLoaded', () => {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabPanels = document.querySelectorAll('.tab-panel');

            tabLinks.forEach(link => {
                link.addEventListener('click', () => {
                    const tabId = link.getAttribute('data-tab');
                    tabLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                    tabPanels.forEach(panel => {
                        panel.classList.toggle('active', panel.id === `tab-${tabId}`);
                    });
                });
            });
        });
    </script>
</body>
</html>