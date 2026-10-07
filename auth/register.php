<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * User Authentication: Registration
 * 
 * Task Reference:
 * - Task-2: User Authentication & Registration Form
 * - Task-4: Security Enhancements (Server/Client Validation, BCRYPT Password Hashing, CSRF)
 */

$pageTitle = 'Create an Account';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(BASE_URL . '/index.php');
}

$errors = [];
$username = '';
$email = '';

// Handle Registration Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token (Task-4)
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid or expired. Please reload and submit again.';
    }

    $username        = trim($_POST['username'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // 2. Server-side Validations (Task-4: Form Validation)
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,25}$/', $username)) {
        $errors[] = 'Username must be between 3 and 25 characters and contain only letters, numbers, and underscores.';
    }

    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // 3. Check for existing username or email using PDO Prepared Statements (Task-4)
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();

            $checkStmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = :username OR email = :email LIMIT 1");
            $checkStmt->execute([
                ':username' => $username,
                ':email'    => $email
            ]);
            $existingUser = $checkStmt->fetch();

            if ($existingUser) {
                if (strtolower($existingUser['username']) === strtolower($username)) {
                    $errors[] = 'The username "' . e($username) . '" is already taken. Please choose another.';
                }
                if (strtolower($existingUser['email']) === strtolower($email)) {
                    $errors[] = 'An account with this email address already exists.';
                }
            } else {
                // 4. Secure Password Hashing (Task-2: Password Hashing)
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Insert into Database with default role 'user'
                $insertStmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (:username, :email, :password, 'user', NOW())");
                $insertStmt->execute([
                    ':username' => $username,
                    ':email'    => $email,
                    ':password' => $hashedPassword
                ]);

                set_flash('success', 'Registration successful! You can now sign in with your credentials.');
                redirect(BASE_URL . '/auth/login.php');
            }

        } catch (PDOException $e) {
            $errors[] = 'Database error during registration: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 text-center pt-4 pb-2">
                    <div class="d-inline-flex p-3 rounded-circle bg-light text-primary mb-2">
                        <i class="bi bi-person-plus-fill fs-2" style="color: #0d7a6f;"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Create an Account</h3>
                    <p class="text-muted small">Join the ApexPlanet Blog platform to publish articles and tutorials</p>
                </div>

                <div class="card-body p-4 pt-2">
                    <!-- Error Notifications -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger shadow-sm border-0 small">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form (Client-Side Validated with Bootstrap 5) -->
                    <form method="POST" action="" id="registerForm" class="needs-validation" novalidate>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold small text-secondary">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" value="<?= e($username) ?>" placeholder="Choose a username (e.g. jdoe)" pattern="^[a-zA-Z0-9_]{3,25}$" required autocomplete="username">
                                <div class="invalid-feedback">Username must be 3-25 alphanumeric characters.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold small text-secondary">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="<?= e($email) ?>" placeholder="name@example.com" required autocomplete="email">
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="reg_password" class="form-label fw-semibold small text-secondary">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="reg_password" name="password" minlength="6" placeholder="Min. 6 chars" required autocomplete="new-password">
                                    <div class="invalid-feedback">Password must be at least 6 characters.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="reg_confirm_password" class="form-label fw-semibold small text-secondary">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="reg_confirm_password" name="confirm_password" placeholder="Repeat password" required autocomplete="new-password">
                                    <div class="invalid-feedback">Passwords must match.</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-4 small text-muted">
                            <input class="form-check-input" type="checkbox" id="termsCheck" required>
                            <label class="form-check-label" for="termsCheck">
                                I agree to the <span class="text-dark fw-semibold">Internship Code of Conduct</span> and publishing guidelines.
                            </label>
                            <div class="invalid-feedback">You must accept the terms before registering.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom py-2 rounded-3 shadow-sm">
                                <i class="bi bi-person-check-fill me-1"></i> Register Account
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-light border-0 py-3 text-center">
                    <span class="text-muted small">Already have an account?</span>
                    <a href="<?= BASE_URL ?>/auth/login.php" class="fw-semibold small ms-1 text-decoration-none" style="color: #0d7a6f;">
                        Sign In Here
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
