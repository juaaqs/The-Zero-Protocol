<?php
// email_helper.php
// Contains functions related to sending emails.

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// Load Composer's autoloader
require_once 'vendor/autoload.php';
// Load our config file for SMTP credentials
require_once 'config.php';

/**
 * Sends a verification email.
 *
 * @param string $toEmail The recipient's email address.
 * @param string $subject The email subject line.
 * @param string $body The HTML body of the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendVerificationEmail($toEmail, $subject, $body) {
    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        // --- Server settings ---
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;      // Enable verbose debug output (for testing)
        $mail->isSMTP();                               // Send using SMTP
        $mail->Host       = SMTP_HOST;                 // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                      // Enable SMTP authentication
        $mail->Username   = SMTP_USERNAME;             // SMTP username
        $mail->Password   = SMTP_PASSWORD;             // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption (or ENCRYPTION_SMTPS for port 465)
        $mail->Port       = SMTP_PORT;                 // TCP port to connect to; use 587 for TLS, 465 for SSL

        // --- Recipients ---
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail);     // Add a recipient

        // --- Content ---
        $mail->isHTML(true);             // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Plain text version for non-HTML mail clients

        $mail->send();
        // echo 'Message has been sent'; // For debugging
        return true;
    } catch (PHPMailerException $e) {
        // Log the detailed error
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"; // For debugging
        return false;
    }
}
?>