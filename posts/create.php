<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * CRUD Operation: Create Post (Task-2: Create)
 * 
 * Task Reference:
 * - Task-2: Create: Develop a PHP form to add new posts to the database.
 * - Task-4: Security Enhancements (PDO Prepared Statements, CSRF, Form Validation)
 */

$pageTitle = 'Write a New Article';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Authentication check: Only registered users can write articles
require_login();

$currentUser = current_user();
$categories = get_categories();
$errors = [];
$title = '';
$category = $categories[0];
$content = '';
$status = 'published';

// Handle Post Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token (Task-4)
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired or invalid. Please resubmit the form.';
    }

    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $status   = trim($_POST['status'] ?? 'published');

    // 2. Server-side Validation (Task-4: Data Integrity)
    if (empty($title)) {
        $errors[] = 'Article title is required.';
    } elseif (mb_strlen($title) < 5) {
        $errors[] = 'Article title must be at least 5 characters long.';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Article title cannot exceed 255 characters.';
    }

    if (empty($category) || !in_array($category, $categories)) {
        $errors[] = 'Please select a valid category from the list.';
    }

    if (empty($content)) {
        $errors[] = 'Article content cannot be empty.';
    } elseif (mb_strlen($content) < 15) {
        $errors[] = 'Article content should have at least 15 characters.';
    }

    if (!in_array($status, ['published', 'draft'])) {
        $status = 'published';
    }

    // 3. Database Insertion with PDO Prepared Statement (Task-4: Prevent SQL Injection)
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO posts (user_id, title, category, content, status, created_at)
                VALUES (:user_id, :title, :category, :content, :status, NOW())
            ");
            $stmt->execute([
                ':user_id'  => $currentUser['id'],
                ':title'    => $title,
                ':category' => $category,
                ':content'  => $content,
                ':status'   => $status
            ]);

            $newPostId = $pdo->lastInsertId();

            set_flash('success', 'Your article "' . e($title) . '" was published successfully!');
            redirect(BASE_URL . '/posts/view.php?id=' . $newPostId);

        } catch (PDOException $e) {
            $errors[] = 'Database error while saving post: ' . $e->getMessage();
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
                    <h2 class="fw-bold text-dark mb-1">Create New Post</h2>
                    <p class="text-muted small mb-0">Share your technical knowledge and internship learnings</p>
                </div>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Feed
                </a>
            </div>

            <!-- Error Alerts -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger shadow-sm border-0 mb-4">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the following issues:</div>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Post Creation Form (Bootstrap Client Validated) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold text-secondary">Post Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-5" id="title" name="title" value="<?= e($title) ?>" placeholder="e.g. Modern Web Architecture with PHP 8 & MySQL" required minlength="5" maxlength="255">
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

                        <!-- Publishing Status -->
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-bold text-secondary">Publication Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="published" <?= ($status === 'published') ? 'selected' : '' ?>>Published (Visible to all)</option>
                                <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft (Saved privately)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Post Body / Content -->
                    <div class="mb-4">
                        <label for="content" class="form-label fw-bold text-secondary">Article Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="12" placeholder="Write your full technical article here... Provide code examples, explanations, and insights." required minlength="15"><?= e($content) ?></textarea>
                        <div class="form-text">Plain text with paragraphs will be preserved and automatically formatted.</div>
                        <div class="invalid-feedback">Article content must contain at least 15 characters.</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                        <a href="<?= BASE_URL ?>/index.php" class="btn btn-light px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-custom px-4 py-2 shadow-sm">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Publish Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
