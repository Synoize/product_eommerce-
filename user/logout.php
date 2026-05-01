<?php
/**
 * User Logout
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Clear all session data
$_SESSION = [];

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

// Destroy session
session_destroy();

// Start new session for flash message
session_start();
setFlash('You have been logged out successfully.', 'success');
redirect(BASE_URL . 'user/login.php');
