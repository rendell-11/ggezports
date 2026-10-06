<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'components/connect.php'; // Database connection

use League\OAuth2\Client\Provider\Google;

$google = new Google([
    'clientId'     => '826560848328-nsoumv48o6kl319kuefd5rq6ovbo5bok.apps.googleusercontent.com',
    'clientSecret' => 'YOUR_GOOGLE_CLIENT_SECRET',
    'redirectUri'  => 'http://localhost/ggezports/google_login.php',
]);

if (!isset($_GET['code'])) {
    // Get authorization URL with extra parameters for 2FA
    $authUrl = $google->getAuthorizationUrl([
        'access_type' => 'offline', // Required for refresh tokens
        'prompt' => 'consent',     // Ensures 2FA and re-authentication
    ]);
    $_SESSION['oauth2state'] = $google->getState();
    header('Location: ' . $authUrl);
    exit();
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    // State is invalid, prevent CSRF attack
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {
    // Exchange authorization code for access token
    $token = $google->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Fetch user’s profile
    $user = $google->getResourceOwner($token);
    $email = $user->getEmail();
    $name = $user->getName();

    // Check if the user exists in your database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user_id = $stmt->fetchColumn();

    if (!$user_id) {
        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        $user_id = $conn->lastInsertId();
    }

    // Set session variables and redirect
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    header("Location: home.php");
    exit();
}
