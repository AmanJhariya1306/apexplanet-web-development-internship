<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * Application Configuration
 * 
 * Task Reference: Task-1 (Environment Setup) & Task-5 (Integration)
 */

// Prevent multiple inclusions
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Start PHP Session securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session parameters
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    // If running on HTTPS, cookie_secure will be set
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Error reporting for development (can be set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Application Constants
define('APP_NAME', 'ApexBlog CMS');
define('APP_TAGLINE', 'Dynamic Web Application with PHP & MySQL');
define('APP_VERSION', '1.0.0');
define('INTERNSHIP_COMPANY', 'ApexPlanet Software Pvt. Ltd.');
define('INTERNSHIP_ROLE', 'Web Development Intern');
define('POSTS_PER_PAGE', 5); // Pagination setting for Task-3

// Base Directory & Dynamic URL Resolution
define('BASE_PATH', dirname(__DIR__));

// Determine dynamic base URL for seamless portability between XAMPP, WAMP, and PHP Built-in server
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Normalize root path for links
if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '') {
    $baseUrl = $protocol . $host;
} else {
    // Find base folder if located in subfolder (e.g. /apexplanet-blog-cms)
    $parts = explode('/', trim($scriptDir, '/'));
    $projectRoot = '';
    // Check if we are inside a subfolder of the project
    $subfolders = ['auth', 'posts', 'admin', 'setup', 'config', 'includes'];
    if (!empty($parts) && in_array(end($parts), $subfolders)) {
        array_pop($parts);
    }
    $projectRoot = implode('/', $parts);
    $baseUrl = $protocol . $host . ($projectRoot ? '/' . $projectRoot : '');
}
define('BASE_URL', rtrim($baseUrl, '/'));
