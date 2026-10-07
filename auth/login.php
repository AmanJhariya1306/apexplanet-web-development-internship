<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * User Authentication: Login
 * 
 * Task Reference:
 * - Task-2: User Authentication & Sessions
 * - Task-4: Security Enhancements (PDO Prepared Statements, CSRF, Input Sanitization)
 */

$pageTitle = 'Login';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(BASE_URL . '/index.php');
}

$errors = [];
$identifier = '';

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token (Task-4)
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid or expired. Please submit the form again.';
    }

    $identifier = trim($_POST['username_or_email'] ?? '');
    $password   = $_POST['password'] ?? '';

    // 2. Server-side Form Validation (Task-4)
    if (empty($identifier)) {
        $errors[] = 'Username or email address is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // 3. Process Authentication
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            
            // Prepared Statement to prevent SQL Injection (Task-4)
            $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE username = :ident OR email = :ident LIMIT 1");
            $stmt->execute([':ident' => $identifier]);
            $user = $stmt->fetch();

            // Verify Password using standard BCRYPT algorithm (Task-2)
            if ($user && password_verify($password, $user['password'])) {
                // Prevent Session Fixation attack
                session_regenerate_id(true);

                // Store user session state (Task-2)
                $_SESSION['user'] = [
                    'id'       => (int)$user['id'],
                    'username' => $user['username'],
                    'email'    => $user['email'],
                    'role'     => $user['role']
                ];

                set_flash('success', 'Welcome back, ' . e($user['username']) . '! You have successfully logged in.');

                // Role-based redirection (Task-4 RBAC)
                if ($user['role'] === 'admin' || $user['role'] === 'editor') {
                    redirect(BASE_URL . '/admin/dashboard.php');
                } else {
                    redirect(BASE_URL . '/index.php');
                }
            } else {
                $errors[] = 'Invalid username/email or password. Please verify and try again.';
            }

        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 text-center pt-4 pb-2">
                    <div class="d-inline-flex p-3 rounded-circle bg-light text-primary mb-2">
                        <i class="bi bi-shield-lock-fill fs-2" style="color: #0d7a6f;"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Welcome Back</h3>
                    <p class="text-muted small">Sign in to manage your blog articles and profile</p>
                </div>

                <div class="card-body p-4 pt-2">
                    <!-- Error Display -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger shadow-sm border-0 small">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form (Client-Side Validated with Bootstrap 5) -->
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="username_or_email" class="form-label fw-semibold small text-secondary">Username or Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="username_or_email" name="username_or_email" value="<?= e($identifier) ?>" placeholder="Enter username or email" required autocomplete="username">
                                <div class="invalid-feedback">Please enter your username or email.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label fw-semibold small text-secondary mb-0">Password</label>
                            </div>
                            <div class="input-group mt-1">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary-custom py-2 rounded-3 shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                            </button>
                        </div>
                    </form>

                    <!-- Quick Testing / Viva Helper Box -->
                    <div class="bg-light p-3 rounded-3 mt-4 border border-light-subtle">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-secondary"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Demo Logins:</span>
                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Click to Autofill</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 btn-demo-fill" data-user="admin" data-pass="admin123">
                                Admin (admin123)
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm py-1 px-2 btn-demo-fill text-dark" data-user="editor" data-pass="editor123">
                                Editor (editor123)
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm py-1 px-2 btn-demo-fill text-dark" data-user="student" data-pass="student123">
                                User (student123)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light border-0 py-3 text-center">
                    <span class="text-muted small">Don't have an account yet?</span>
                    <a href="<?= BASE_URL ?>/auth/register.php" class="fw-semibold small ms-1 text-decoration-none" style="color: #0d7a6f;">
                        Register Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
