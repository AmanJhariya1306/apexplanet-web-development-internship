<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * Administration Dashboard (Task-4: Role-Based Access Control)
 * 
 * Task Reference:
 * - Task-4: User Roles and Permissions (Admin & Editor Dashboard)
 * - Task-5: Full Application Integration
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// RBAC Middleware: Only Admin or Editor can access this portal
require_editor_or_admin();

$currentUser = current_user();
$pdo = getDBConnection();

// Fetch System Statistics
$totalPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$publishedPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$draftPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Fetch Recent 6 Posts
$recentPostsStmt = $pdo->query("
    SELECT posts.*, users.username, users.role AS author_role
    FROM posts
    JOIN users ON posts.user_id = users.id
    ORDER BY posts.created_at DESC
    LIMIT 6
");
$recentPosts = $recentPostsStmt->fetchAll();

// If Admin, Fetch Recent Registered Users
$recentUsers = [];
if (is_admin()) {
    $recentUsers = $pdo->query("
        SELECT id, username, email, role, created_at,
        (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) AS post_count
        FROM users
        ORDER BY created_at DESC
        LIMIT 5
    ")->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <!-- Top Welcome Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h2 class="fw-bold text-dark mb-0">Management Portal</h2>
                <?php if (is_admin()): ?>
                    <span class="badge bg-danger">Administrator</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Editor</span>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-0">Signed in as <strong><?= e($currentUser['username']) ?></strong> (<?= e($currentUser['email']) ?>)</p>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/posts/create.php" class="btn btn-primary-custom btn-sm rounded-pill px-3 py-2">
                <i class="bi bi-pencil-square me-1"></i> New Article
            </a>
            <a href="<?= BASE_URL ?>/admin/posts.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                <i class="bi bi-folder2-open me-1"></i> Manage All Posts
            </a>
            <?php if (is_admin()): ?>
                <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2">
                    <i class="bi bi-people-fill me-1"></i> Manage Users
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-5">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalPosts ?></div>
                    <div class="text-muted small">Total Articles</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $publishedPosts ?></div>
                    <div class="text-muted small">Published</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-file-earmark-diff"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $draftPosts ?></div>
                    <div class="text-muted small">Drafts</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalUsers ?></div>
                    <div class="text-muted small">Registered Users</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Posts Table -->
        <div class="col-lg-<?= is_admin() ? '8' : '12' ?>">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark">Recent Articles</h5>
                    <a href="<?= BASE_URL ?>/admin/posts.php" class="text-decoration-none small fw-semibold">View All <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentPosts)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No posts available yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentPosts as $post): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/posts/view.php?id=<?= (int)$post['id'] ?>" class="text-decoration-none fw-semibold text-dark">
                                                <?= e(truncate_text($post['title'], 45)) ?>
                                            </a>
                                            <div class="text-muted" style="font-size: 0.75rem;"><?= time_ago($post['created_at']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= e($post['username']) ?></span>
                                        </td>
                                        <td>
                                            <span class="small text-secondary"><?= e($post['category']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($post['status'] === 'published'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Published</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= BASE_URL ?>/posts/view.php?id=<?= (int)$post['id'] ?>" class="btn btn-light" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/posts/edit.php?id=<?= (int)$post['id'] ?>" class="btn btn-light" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if (can_delete_post($post)): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/posts/delete.php" class="d-inline" onsubmit="return confirm('Delete this post?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                                        <button type="submit" class="btn btn-light text-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Registered Users (Admin View) -->
        <?php if (is_admin()): ?>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0 text-dark">User Directory</h5>
                        <a href="<?= BASE_URL ?>/admin/users.php" class="text-decoration-none small fw-semibold">Manage <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentUsers as $user): ?>
                                <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="post-author-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold small text-dark"><?= e($user['username']) ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;"><?= e($user['email']) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php elseif ($user['role'] === 'editor'): ?>
                                            <span class="badge bg-warning text-dark">Editor</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border">User</span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="card-footer bg-light border-0 text-center py-2">
                        <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-danger btn-sm w-100 rounded-pill">
                            <i class="bi bi-person-gear me-1"></i> Edit User Roles
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
