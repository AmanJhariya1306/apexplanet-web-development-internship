<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * Database Connection using PDO (PHP Data Objects)
 * 
 * Task Reference:
 * - Task-2: Database Setup (database 'blog', tables 'posts', 'users')
 * - Task-4: Security Enhancements (PDO Prepared Statements to prevent SQL Injection)
 */

require_once __DIR__ . '/config.php';

// Database Credentials (Default XAMPP / WAMP / MAMP configuration)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'blog');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO database connection instance.
 * Configured with strict error reporting and prepared statement emulation disabled.
 *
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Native prepared statements
            PDO::ATTR_TIMEOUT            => 5,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database 'blog' does not exist yet (Error 1049 in MySQL)
            if ($e->getCode() == 1049) {
                // If the user hasn't run the installer yet, redirect or show friendly prompt
                $installUrl = BASE_URL . '/setup/install.php';
                if (!headers_sent() && basename($_SERVER['SCRIPT_NAME']) !== 'install.php') {
                    header("Location: $installUrl");
                    exit;
                }
            }

            // Friendly error screen for database connection troubleshooting
            $errorMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            die("
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Database Connection Error - ApexBlog</title>
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            </head>
            <body class='bg-light py-5'>
                <div class='container' style='max-width: 650px;'>
                    <div class='card shadow-sm border-danger'>
                        <div class='card-header bg-danger text-white py-3'>
                            <h4 class='mb-0'>⚠️ Database Connection Error</h4>
                        </div>
                        <div class='card-body p-4'>
                            <p class='text-muted'>Could not establish a connection to MySQL database <strong>" . DB_NAME . "</strong>.</p>
                            <div class='alert alert-secondary font-monospace small mb-4'>$errorMsg</div>
                            <h5>Troubleshooting Steps:</h5>
                            <ol class='mb-4'>
                                <li>Make sure <strong>Apache</strong> and <strong>MySQL</strong> are running in XAMPP / WAMP Control Panel.</li>
                                <li>If the <strong>'blog'</strong> database is not created yet, run our 1-click installer:</li>
                            </ol>
                            <div class='d-grid gap-2'>
                                <a href='" . BASE_URL . "/setup/install.php' class='btn btn-primary btn-lg'>🚀 Run 1-Click Database Setup</a>
                                <a href='javascript:location.reload()' class='btn btn-outline-secondary'>🔄 Retry Connection</a>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ");
        }
    }

    return $pdo;
}
