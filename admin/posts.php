<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * Posts Management Portal (Task-4 & Task-5)
 * 
 * Accessible by Administrators and Editors
 */

$pageTitle = 'Manage Articles';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// RBAC Authorization
require_editor_or_admin();

$pdo = getDBConnection();
$currentUser = current_user();

$searchQuery = trim($_GET['q'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$categories = get_categories();

// Build Query
$where = [];
$params = [];

if ($searchQuery !== '') {
    $where[] = "(posts.title LIKE :q OR posts.content LIKE :q)";
    $params[':q'] = '%' . $searchQuery . '%';
}

if ($categoryFilter !== '' && in_array($categoryFilter, $categories)) {
    $where[] = "posts.category = :category";
    $params[':category'] = $categoryFilter;
}

if ($statusFilter !== '' && in_array($statusFilter, ['published', 'draft'])) {
    $where[] = "posts.status = :status";
    $params[':status'] = $statusFilter;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT posts.*, users.username, users.role AS author_role
    FROM posts
    JOIN users ON posts.user_id = users.id
    $whereSql
    ORDER BY posts.created_at DESC
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Manage Articles</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">Article Management Directory</h2>
            <p class="text-muted small mb-0">View, moderate, and edit all published and draft articles across the system</p>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/posts/create.php" class="btn btn-primary-custom btn-sm rounded-pill px-3 py-2">
                <i class="bi bi-pencil-square me-1"></i> New Article
            </a>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by title or content..." value="<?= e($searchQuery) ?>">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm flex-grow-1">Filter</button>
                <?php if ($searchQuery !== '' || $categoryFilter !== '' || $statusFilter !== ''): ?>
                    <a href="<?= BASE_URL ?>/admin/posts.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Posts Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date Published</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No articles match the specified filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td class="text-muted small">#<?= (int)$post['id'] ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/posts/view.php?id=<?= (int)$post['id'] ?>" class="text-decoration-none fw-semibold text-dark">
                                        <?= e(truncate_text($post['title'], 55)) ?>
                                    </a>
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
                                <td class="small text-muted">
                                    <?= date('M d, Y', strtotime($post['created_at'])) ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/posts/view.php?id=<?= (int)$post['id'] ?>" class="btn btn-light" title="View Article">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/posts/edit.php?id=<?= (int)$post['id'] ?>" class="btn btn-light" title="Edit Article">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if (can_delete_post($post)): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/posts/delete.php" class="d-inline" onsubmit="return confirm('Permanently delete <?= e($post['title']) ?>?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                                <button type="submit" class="btn btn-light text-danger" title="Delete Article">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
