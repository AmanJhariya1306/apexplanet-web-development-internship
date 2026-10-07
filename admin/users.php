<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * User Roles and Permissions Management (Task-4: RBAC)
 * 
 * Task Reference:
 * - Task-4: User Roles and Permissions: Extend user table to include roles (admin, editor, user).
 *           Implement role-based access control for different parts of the application.
 */

$pageTitle = 'Manage User Roles & Permissions';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Strict RBAC: Only Administrators are permitted to manage users and roles
require_admin();

$currentUser = current_user();
$pdo = getDBConnection();
$errors = [];

// Handle Role Update or Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Invalid security token.');
        redirect(BASE_URL . '/admin/users.php');
    }

    $action = $_POST['action'] ?? '';
    $targetUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if (!$targetUserId) {
        set_flash('danger', 'Invalid target user ID.');
        redirect(BASE_URL . '/admin/users.php');
    }

    // 1. Update User Role
    if ($action === 'update_role') {
        $newRole = trim($_POST['role'] ?? '');
        $allowedRoles = ['admin', 'editor', 'user'];

        if (!in_array($newRole, $allowedRoles)) {
            set_flash('danger', 'Invalid role selected.');
            redirect(BASE_URL . '/admin/users.php');
        }

        // Safety Guard: Admin cannot demote their own current logged-in account
        if ($targetUserId === (int)$currentUser['id'] && $newRole !== 'admin') {
            set_flash('danger', 'Security Guard: You cannot demote your own administrator account.');
            redirect(BASE_URL . '/admin/users.php');
        }

        try {
            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute([
                ':role' => $newRole,
                ':id'   => $targetUserId
            ]);
            set_flash('success', "User role updated successfully to '" . ucfirst($newRole) . "'.");
        } catch (PDOException $e) {
            set_flash('danger', 'Database error: ' . $e->getMessage());
        }
        redirect(BASE_URL . '/admin/users.php');
    }

    // 2. Delete User Account
    if ($action === 'delete_user') {
        // Safety Guard: Cannot delete self
        if ($targetUserId === (int)$currentUser['id']) {
            set_flash('danger', 'Security Guard: You cannot delete your own account while logged in.');
            redirect(BASE_URL . '/admin/users.php');
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $targetUserId]);
            set_flash('success', 'User account and associated articles deleted successfully.');
        } catch (PDOException $e) {
            set_flash('danger', 'Database error: ' . $e->getMessage());
        }
        redirect(BASE_URL . '/admin/users.php');
    }
}

// Fetch all users with their post counts
$usersStmt = $pdo->query("
    SELECT users.*, 
    (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) AS post_count
    FROM users 
    ORDER BY users.created_at ASC
");
$users = $usersStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Users & RBAC</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">User Roles & Permissions</h2>
            <p class="text-muted small mb-0">Role-Based Access Control (RBAC) Management System (Task-4)</p>
        </div>

        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Security Role Information Banner -->
    <div class="alert alert-light border shadow-sm mb-4">
        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-check text-success me-1"></i> Role-Based Access Hierarchy:</h6>
        <div class="row g-2 small text-muted">
            <div class="col-md-4">
                <strong class="text-danger">Admin:</strong> Complete system control, user role management, moderate/delete any post.
            </div>
            <div class="col-md-4">
                <strong class="text-warning text-dark">Editor:</strong> Access dashboard, view, edit, and moderate posts from any author.
            </div>
            <div class="col-md-4">
                <strong class="text-info text-dark">User / Author:</strong> Create personal posts, edit/delete only own posts.
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Assigned Role</th>
                        <th>Articles</th>
                        <th>Registered Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="text-muted small">#<?= (int)$u['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="post-author-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark">
                                            <?= e($u['username']) ?>
                                            <?php if ((int)$u['id'] === (int)$currentUser['id']): ?>
                                                <span class="badge bg-secondary-subtle text-secondary small">(You)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($u['email']) ?></td>
                            <td>
                                <form method="POST" action="" class="d-inline-flex align-items-center gap-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update_role">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">

                                    <select name="role" class="form-select form-select-sm" style="width: 110px;" <?= ((int)$u['id'] === (int)$currentUser['id']) ? 'disabled' : '' ?> onchange="this.form.submit()">
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="editor" <?= $u['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                        <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/index.php?user_id=<?= (int)$u['id'] ?>" class="badge bg-light text-dark border text-decoration-none">
                                    <?= (int)$u['post_count'] ?> posts
                                </a>
                            </td>
                            <td class="small text-muted">
                                <?= date('M d, Y', strtotime($u['created_at'])) ?>
                            </td>
                            <td class="text-end">
                                <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Warning: Deleting user <?= e($u['username']) ?> will also delete all their posts. Proceed?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3" title="Delete User">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Active Account</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
