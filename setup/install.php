<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * 1-Click Database Setup & Seeder Wizard
 * 
 * Task Reference: Task-1 (Environment Setup) & Task-2 (Database Setup)
 */

require_once __DIR__ . '/../config/config.php';

$message = '';
$messageType = '';
$installed = false;
$stepLogs = [];

// Handle Setup Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_name'] ?? 'blog');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = trim($_POST['db_pass'] ?? '');

    try {
        // Step 1: Connect to MySQL server (without selecting database yet)
        $dsnWithoutDb = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
        $pdoServer = new PDO($dsnWithoutDb, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $stepLogs[] = "✅ Connected to MySQL server successfully at <code>$dbHost:$dbPort</code>.";

        // Step 2: Create database if not exists
        $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $stepLogs[] = "✅ Database <code>$dbName</code> verified/created.";

        // Step 3: Connect directly to the database
        $dsnWithDb = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
        $pdo = new PDO($dsnWithDb, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Step 4: Drop old tables if overwrite requested
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("DROP TABLE IF EXISTS `posts`;");
        $pdo->exec("DROP TABLE IF EXISTS `users`;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $stepLogs[] = "✅ Cleaned up old tables if present.";

        // Step 5: Create Users Table (Task-2 & Task-4)
        $pdo->exec("
            CREATE TABLE `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(50) NOT NULL UNIQUE,
                `email` VARCHAR(100) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `role` ENUM('admin', 'editor', 'user') NOT NULL DEFAULT 'user',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $stepLogs[] = "✅ Table <code>users</code> created with RBAC role column (admin, editor, user).";

        // Step 6: Create Posts Table (Task-2, 3, 4)
        $pdo->exec("
            CREATE TABLE `posts` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `category` VARCHAR(50) NOT NULL DEFAULT 'Web Development',
                `content` TEXT NOT NULL,
                `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`user_id`),
                INDEX (`category`),
                INDEX (`status`),
                CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        $stepLogs[] = "✅ Table <code>posts</code> created with foreign keys and indexes.";

        // Step 7: Seed Users with secure password_hash (BCRYPT)
        $userStmt = $pdo->prepare("INSERT INTO `users` (username, email, password, role) VALUES (:username, :email, :password, :role)");

        // 1. Admin
        $userStmt->execute([
            ':username' => 'admin',
            ':email'    => 'admin@apexplanet.in',
            ':password' => password_hash('admin123', PASSWORD_BCRYPT),
            ':role'     => 'admin'
        ]);
        $adminId = $pdo->lastInsertId();

        // 2. Editor
        $userStmt->execute([
            ':username' => 'editor',
            ':email'    => 'editor@apexplanet.in',
            ':password' => password_hash('editor123', PASSWORD_BCRYPT),
            ':role'     => 'editor'
        ]);
        $editorId = $pdo->lastInsertId();

        // 3. Student/User
        $userStmt->execute([
            ':username' => 'student',
            ':email'    => 'student@apexplanet.in',
            ':password' => password_hash('student123', PASSWORD_BCRYPT),
            ':role'     => 'user'
        ]);
        $studentId = $pdo->lastInsertId();

        $stepLogs[] = "✅ Seeded 3 default accounts with secure BCRYPT password hashing.";

        // Step 8: Seed realistic sample posts
        $postStmt = $pdo->prepare("INSERT INTO `posts` (user_id, title, category, content, status, created_at) VALUES (:user_id, :title, :category, :content, :status, :created_at)");

        $posts = [
            [
                'user_id' => $adminId,
                'title' => 'Getting Started with Modern PHP 8 and Apache Server',
                'category' => 'Web Development',
                'content' => "Setting up a robust development environment is the foundation of full-stack web engineering. Throughout the ApexPlanet Web Development internship program, we configured Apache, MySQL, and PHP alongside VS Code and Git.\n\nPHP 8 brings substantial improvements to the table, including Just-In-Time (JIT) compilation, named arguments, attributes, constructor property promotion, match expressions, and union types. When coupled with an organized directory structure and version control, building scalable web systems becomes an intuitive and productive workflow. In this article, we examine essential php.ini configurations, virtual hosts, and debugging best practices.",
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'user_id' => $editorId,
                'title' => 'Building Robust CRUD Systems with PHP PDO and Prepared Statements',
                'category' => 'PHP & MySQL',
                'content' => "Data persistence is at the heart of dynamic applications. While legacy PHP code often utilized mysql_* or basic mysqli queries prone to concatenation bugs, modern industry standards strictly mandate PHP Data Objects (PDO) with prepared statements.\n\nPrepared statements decouple the SQL command structure from user-supplied parameters. The database engine pre-compiles the query template, treating injected user strings purely as literal data values. This completely eliminates SQL injection vulnerabilities (SQLi). Additionally, PDO provides database abstraction, clean exception-based error reporting, and uniform fetch modes across relational database engines.",
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
            ],
            [
                'user_id' => $studentId,
                'title' => 'Designing Efficient Pagination and Search for High-Volume Web Listings',
                'category' => 'Software Engineering',
                'content' => "As database tables grow into thousands of records, retrieving all rows in a single SQL query degrades server response times and floods client browsers.\n\nImplementing server-side pagination with SQL LIMIT and OFFSET clauses ensures fast, constant-time database queries. Combined with full-text or parameterized LIKE filters across titles and post bodies, users can quickly locate relevant articles. A critical engineering detail is preserving active search filters and sorting flags when navigating across pagination page numbers, which we solve elegantly via query string concatenation.",
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'user_id' => $adminId,
                'title' => 'Securing Web Applications Against Common Vulnerabilities (OWASP Top 10)',
                'category' => 'Security & DevOps',
                'content' => "In modern web development, security cannot be an afterthought. Every production web application must defend against three primary attack vectors:\n\n1. SQL Injection (SQLi): Mitigated using PDO prepared statements.\n2. Cross-Site Scripting (XSS): Mitigated by escaping all dynamic outputs via htmlspecialchars with UTF-8 flags.\n3. Cross-Site Request Forgery (CSRF): Prevented using cryptographically secure anti-CSRF session tokens on all state-altering POST requests.\n4. Session Hijacking & Fixation: Mitigated by regenerating session identifiers upon authentication and enforcing HttpOnly cookie flags.",
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'user_id' => $editorId,
                'title' => 'Role-Based Access Control (RBAC) in Enterprise Web Portals',
                'category' => 'Software Engineering',
                'content' => "Role-Based Access Control (RBAC) restricts system access based on assigned user roles. In our Blog CMS application, we defined three principal tiers:\n\n- Administrator: Full system oversight, managing user permissions, content moderation, and site configuration.\n- Editor: Ability to review, modify, and publish articles across all authors.\n- Author / User: Ability to write and manage personal submissions.\n- Guest / Public: Read-only access to published content with search capabilities.\n\nStructuring clean middleware guards and declarative permission helper functions in PHP ensures that authorization logic remains maintainable and auditable.",
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 days'))
            ],
            [
                'user_id' => $studentId,
                'title' => 'My 7th Semester Internship Experience at ApexPlanet Software Pvt Ltd',
                'category' => 'Internship Experience',
                'content' => "Over the 45-day internship at ApexPlanet Software Pvt Ltd, I traversed the complete web engineering lifecycle: from local environment orchestration and database normalization to developing authentication systems, search filters, pagination algorithms, and comprehensive application hardening.\n\nBuilding this cohesive PHP and MySQL Blog CMS has bridged theoretical academic concepts with real-world industry engineering standards. I am proud to present this system as my 7th semester internship capstone project.",
                'status' => 'published',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        foreach ($posts as $post) {
            $postStmt->execute([
                ':user_id'    => $post['user_id'],
                ':title'      => $post['title'],
                ':category'   => $post['category'],
                ':content'    => $post['content'],
                ':status'     => $post['status'],
                ':created_at' => $post['created_at']
            ]);
        }
        $stepLogs[] = "✅ Seeded 6 comprehensive technical articles for pagination & search demo.";

        $installed = true;
        $message = "Installation completed successfully! Your ApexBlog CMS is ready to use.";
        $messageType = "success";

    } catch (PDOException $e) {
        $message = "Database Installation Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1-Click Database Setup - ApexBlog CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        .setup-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .header-brand {
            background: linear-gradient(135deg, #0d7a6f 0%, #064e46 100%);
            color: #ffffff;
            padding: 30px;
        }
        .badge-step {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            margin-right: 8px;
        }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 760px;">
        <div class="card setup-card mb-4">
            <div class="header-brand text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-white text-teal rounded-circle p-3 mb-3 shadow-sm" style="color: #0d7a6f;">
                    <i class="bi bi-rocket-takeoff-fill fs-2"></i>
                </div>
                <h2 class="fw-bold mb-1">ApexBlog Database Setup Wizard</h2>
                <p class="mb-0 text-white-50">ApexPlanet Software Pvt Ltd • 7th Semester Internship Final Project</p>
            </div>

            <div class="card-body p-4 p-md-5">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <strong><?= $messageType === 'success' ? '🎉 Success!' : '⚠️ Attention:' ?></strong> <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($installed): ?>
                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        <h5 class="fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Database Configured Successfully!</h5>
                        <p class="mb-0">All tables, constraints, foreign keys, RBAC roles, and initial seed articles have been created in database <code>blog</code>.</p>
                    </div>

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-key-fill text-primary me-2"></i>Default Credentials (Auto-Generated & Ready):</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered bg-white mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Role</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Password</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-danger">Admin</span></td>
                                            <td><code>admin</code></td>
                                            <td>admin@apexplanet.in</td>
                                            <td><code>admin123</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-warning text-dark">Editor</span></td>
                                            <td><code>editor</code></td>
                                            <td>editor@apexplanet.in</td>
                                            <td><code>editor123</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-info text-dark">User / Author</span></td>
                                            <td><code>student</code></td>
                                            <td>student@apexplanet.in</td>
                                            <td><code>student123</code></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-2 text-secondary">Setup Log:</h6>
                    <ul class="list-group mb-4 small">
                        <?php foreach ($stepLogs as $log): ?>
                            <li class="list-group-item bg-light"><?= $log ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= BASE_URL ?>/index.php" class="btn btn-success flex-grow-1 py-2 fw-semibold">
                            <i class="bi bi-house-door-fill me-1"></i> Visit Homepage
                        </a>
                        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary flex-grow-1 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Log In Now
                        </a>
                    </div>

                <?php else: ?>
                    <p class="text-muted">
                        This wizard will initialize the <strong><code>blog</code></strong> database, create the <strong><code>users</code></strong> and <strong><code>posts</code></strong> tables, and pre-populate realistic sample content for your internship demo.
                    </p>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="install">

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Database Host</label>
                                <input type="text" name="db_host" class="form-control" value="localhost" required>
                                <div class="form-text">Default for XAMPP / WAMP is <code>localhost</code></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Port</label>
                                <input type="text" name="db_port" class="form-control" value="3306" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Database Name</label>
                            <input type="text" name="db_name" class="form-control" value="blog" required>
                            <div class="form-text">Specified in ApexPlanet Internship Task-2</div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">MySQL Username</label>
                                <input type="text" name="db_user" class="form-control" value="root" required>
                                <div class="form-text">Default for XAMPP / WAMP is <code>root</code></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">MySQL Password</label>
                                <input type="password" name="db_pass" class="form-control" placeholder="(Leave blank if none)">
                                <div class="form-text">Default for XAMPP is blank</div>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 small d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                            <div>Running this installer will build the tables and insert demo users (Admin, Editor, Author) and 6 test articles.</div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg fw-semibold" style="background-color: #0d7a6f; border-color: #0d7a6f;">
                                <i class="bi bi-play-circle-fill me-2"></i> Install & Initialize Database
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-light text-center py-3 text-muted small">
                ApexPlanet Web Development (PHP, MySQL) 45-Days Internship Project
            </div>
        </div>
    </div>
</body>
</html>
