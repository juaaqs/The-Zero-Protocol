<?php
// handle_avatar_upload.php
// Manages the upload of a new profile picture (avatar) using Base64 cropped data.

session_start();
require 'db_config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$uploadDir = 'assets/uploads/avatars/'; // Ensure this folder exists and is writable

// --- Message Helper Function ---
function setMessage($type, $text) {
    $_SESSION['message'] = ['type' => $type, 'text' => $text];
    header("Location: profile.php");
    exit();
}

// 1. Get the Base64 data from the hidden field
$imageData = $_POST['cropped_image_data'] ?? null;

if (empty($imageData)) {
    setMessage('error', 'No cropped image data received.');
}

// 2. Process the Base64 string (e.g., "data:image/jpeg;base64,.....")
try {
    list($type, $imageData) = explode(';', $imageData);
    list(, $imageData) = explode(',', $imageData);
} catch (Exception $e) {
    setMessage('error', 'Invalid image data format.');
}

// Decode the raw image data
$decodedImage = base64_decode($imageData);

if ($decodedImage === false) {
    setMessage('error', 'Failed to decode image data.');
}

// 3. Generate a secure, unique name for the file
$fileNameNew = $user_id . '_' . time() . '.jpeg';
$fileDestination = $uploadDir . $fileNameNew;
$dbPath = $fileDestination; 

// 4. Save the file to the server
if (file_put_contents($fileDestination, $decodedImage)) {
    
    try {
        // 5. Update the database record
        $update_stmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE user_id = ?");
        $update_stmt->execute([$dbPath, $user_id]);
        
        // 6. FIX (Issue #3 & #4): Update the session variable immediately WITH A CACHE-BUSTER
        $_SESSION['avatar_url'] = $dbPath . '?' . time(); 

        setMessage('success', 'Dossier photo updated successfully.');

    } catch (PDOException $e) {
        error_log("Avatar DB Update Error: " . $e->getMessage());
        // Clean up the file if the DB update fails
        unlink($fileDestination); 
        setMessage('error', 'Database error: Could not save file path.');
    }

} else {
    setMessage('error', 'Could not save the image file to the server. Check folder permissions.');
}
?>