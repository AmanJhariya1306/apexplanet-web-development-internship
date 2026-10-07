<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * CRUD Operation: Read Single Post (Task-2: Read)
 * 
 * Task Reference:
 * - Task-2: Read: Display a post from the database.
 * - Task-4: Security Enhancements (XSS Prevention, RBAC Access, PDO)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

$postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$postId) {
    set_flash('danger', 'Invalid or missing post identifier.');
    redirect(BASE_URL . '/index.php');
}

$post = null;
try {
    $pdo = getDBConnection();
    // Prepared Statement to prevent SQL Injection
    $stmt = $pdo->prepare("
        SELECT posts.*, users.username, users.email, users.role AS author_role
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE posts.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $postId]);
    $post = $stmt->fetch();

} catch (PDOException $e) {
    die("Database Error: " . e($e->getMessage()));
}

if (!$post) {
    $pageTitle = 'Post Not Found';
    require_once __DIR__ . '/../includes/header.php';
    echo "
    <div class='container py-5 text-center'>
        <div class='card p-5 border-0 shadow-sm rounded-4 mx-auto' style='max-width: 500px;'>
            <div class='display-1 text-muted mb-3'><i class='bi bi-file-earmark-x'></i></div>
            <h3 class='fw-bold'>Post Not Found</h3>
            <p class='text-muted'>The requested article does not exist or has been removed.</p>
            <a href='" . BASE_URL . "/index.php' class='btn btn-primary-custom mt-3'>Back to Articles</a>
        </div>
    </div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Check Draft permissions
$currentUser = current_user();
if ($post['status'] === 'draft' && !can_edit_post($post)) {
    set_flash('warning', 'This post is currently unpublished and pending review.');
    redirect(BASE_URL . '/index.php');
}

$pageTitle = $post['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="article-header py-5 border-bottom bg-white">
    <div class="container" style="max-width: 860px;">
        <!-- Category & Draft Pill -->
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="<?= BASE_URL ?>/index.php?category=<?= urlencode($post['category']) ?>" class="post-category-badge text-decoration-none">
                <?= e($post['category']) ?>
            </a>
            <?php if ($post['status'] === 'draft'): ?>
                <span class="badge bg-secondary">Draft</span>
            <?php endif; ?>
        </div>

        <!-- Post Title -->
        <h1 class="display-6 fw-bold text-dark mb-4" style="line-height: 1.3;">
            <?= e($post['title']) ?>
        </h1>

        <!-- Author Meta & Publication Date -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-top pt-3">
            <div class="d-flex align-items-center gap-3">
                <span class="post-author-avatar" style="width: 44px; height: 44px; font-size: 1.1rem;">
                    <?= strtoupper(substr($post['username'], 0, 1)) ?>
                </span>
                <div>
                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                        <span><?= e($post['username']) ?></span>
                        <?php if ($post['author_role'] === 'admin'): ?>
                            <span class="badge bg-danger" style="font-size: 0.65rem;">Admin</span>
                        <?php elseif ($post['author_role'] === 'editor'): ?>
                            <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Editor</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted small">
                        Published on <?= date('M d, Y', strtotime($post['created_at'])) ?>
                        (<?= time_ago($post['created_at']) ?>) • <?= read_time($post['content']) ?> min read
                    </div>
                </div>
            </div>

            <!-- Author / Admin Actions -->
            <div class="d-flex align-items-center gap-2">
                <?php if (can_edit_post($post)): ?>
                    <a href="<?= BASE_URL ?>/posts/edit.php?id=<?= (int)$post['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-pencil-square me-1"></i> Edit
                    </a>
                <?php endif; ?>

                <?php if (can_delete_post($post)): ?>
                    <form method="POST" action="<?= BASE_URL ?>/posts/delete.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this article?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container py-5" style="max-width: 860px;">
    <!-- Article Body -->
    <article class="article-content bg-white p-4 p-md-5 rounded-4 shadow-sm border border-light-subtle">
        <?= nl2br(e($post['content'])) ?>
    </article>

    <!-- Navigation Back Link -->
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-light rounded-pill px-4 text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to All Articles
        </a>
        <a href="<?= BASE_URL ?>/index.php?category=<?= urlencode($post['category']) ?>" class="small text-muted text-decoration-none">
            More in <strong><?= e($post['category']) ?></strong> <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
