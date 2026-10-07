<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * CRUD Operation: Update Post (Task-2: Update)
 * 
 * Task Reference:
 * - Task-2: Update: Implement functionality to edit existing posts.
 * - Task-4: Security Enhancements (RBAC Permission Check, PDO, CSRF)
 */

$pageTitle = 'Edit Post';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Authentication Check
require_login();

$postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$postId) {
    set_flash('danger', 'Invalid post ID.');
    redirect(BASE_URL . '/index.php');
}

$pdo = getDBConnection();

// Fetch Post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $postId]);
$post = $stmt->fetch();

if (!$post) {
    set_flash('danger', 'Post not found.');
    redirect(BASE_URL . '/index.php');
}

// Authorization Check (Task-4: User Roles and Permissions)
// Only author of the post OR an editor/admin can edit
if (!can_edit_post($post)) {
    set_flash('danger', 'Access denied. You do not have permission to edit this post.');
    redirect(BASE_URL . '/index.php');
}

$categories = get_categories();
$errors = [];
$title = $post['title'];
$category = $post['category'];
$content = $post['content'];
$status = $post['status'];

// Handle Post Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token (Task-4)
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid. Please reload and submit again.';
    }

    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $status   = trim($_POST['status'] ?? 'published');

    // 2. Server-side Validation
    if (empty($title)) {
        $errors[] = 'Post title is required.';
    } elseif (mb_strlen($title) < 5 || mb_strlen($title) > 255) {
        $errors[] = 'Post title must be between 5 and 255 characters.';
    }

    if (empty($category) || !in_array($category, $categories)) {
        $errors[] = 'Please select a valid category.';
    }

    if (empty($content) || mb_strlen($content) < 15) {
        $errors[] = 'Post content must contain at least 15 characters.';
    }

    if (!in_array($status, ['published', 'draft'])) {
        $status = 'published';
    }

    // 3. Update Database via Prepared Statement (Task-4)
    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE posts 
                SET title = :title, category = :category, content = :content, status = :status, updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':title'    => $title,
                ':category' => $category,
                ':content'  => $content,
                ':status'   => $status,
                ':id'       => $postId
            ]);

            set_flash('success', 'Article updated successfully!');
            redirect(BASE_URL . '/posts/view.php?id=' . $postId);

        } catch (PDOException $e) {
            $errors[] = 'Database update error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Edit Article</h2>
                    <p class="text-muted small mb-0">Modify your article details and update publication status</p>
                </div>
                <a href="<?= BASE_URL ?>/posts/view.php?id=<?= (int)$postId ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
            </div>

            <!-- Error Alerts -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger shadow-sm border-0 mb-4">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please resolve the following errors:</div>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Post Edit Form -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold text-secondary">Post Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-5" id="title" name="title" value="<?= e($title) ?>" required minlength="5" maxlength="255">
                        <div class="invalid-feedback">Title must be between 5 and 255 characters.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Category -->
                        <div class="col-md-6">
                            <label for="category" class="form-label fw-bold text-secondary">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat) ?>" <?= ($category === $cat) ? 'selected' : '' ?>>
                                        <?= e($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please choose a category.</div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-bold text-secondary">Publication Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="published" <?= ($status === 'published') ? 'selected' : '' ?>>Published (Visible to all)</option>
                                <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft (Saved privately)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-4">
                        <label for="content" class="form-label fw-bold text-secondary">Article Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="12" required minlength="15"><?= e($content) ?></textarea>
                        <div class="invalid-feedback">Article content must contain at least 15 characters.</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <a href="<?= BASE_URL ?>/posts/view.php?id=<?= (int)$postId ?>" class="btn btn-light px-3">
                            Discard Changes
                        </a>
                        <button type="submit" class="btn btn-primary-custom px-4 py-2 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Save & Update Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
