<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * User Authentication: Logout
 * 
 * Task Reference: Task-2: Session Management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

// Clear session data
$_SESSION = [];

// Delete session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start fresh session to store the flash message
session_start();
set_flash('info', 'You have been safely signed out. Come back soon!');
redirect(BASE_URL . '/index.php');
