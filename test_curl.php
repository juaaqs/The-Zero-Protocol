<?php
echo "<h1>Testing cURL HTTPS Request...</h1>";

// URL to test
$url = 'https://www.google.com';

// Initialize cURL
$ch = curl_init();

// Set options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as string
curl_setopt($ch, CURLOPT_HEADER, false); // Don't include headers in output
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Explicitly enable peer verification
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Verify common name exists and matches host

// Explicitly set the CA bundle path (use the one from your php.ini)
// Make sure this path is EXACTLY correct, using backslashes
curl_setopt($ch, CURLOPT_CAINFO, 'D:\xampp\php\extras\cacert.pem');

// Execute the request
$response = curl_exec($ch);

// Check for errors
if(curl_errno($ch)){
    echo '<h2>cURL Error:</h2>';
    echo '<pre>' . curl_error($ch) . '</pre>';
    echo '<p>Error Number: ' . curl_errno($ch) . '</p>';
} else {
    echo '<h2>cURL Success!</h2>';
    echo '<p>Successfully fetched ' . $url . '</p>';
    // Optionally display a small part of the response:
    // echo '<pre>' . htmlspecialchars(substr($response, 0, 200)) . '...</pre>';
}

// Close cURL resource
curl_close($ch);
?>