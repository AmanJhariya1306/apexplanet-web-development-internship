<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * CRUD Operation: Delete Post (Task-2: Delete)
 * 
 * Task Reference:
 * - Task-2: Delete: Add functionality to delete posts.
 * - Task-4: Security Enhancements (CSRF Guard, Prepared Statement, RBAC Authorization)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// Authentication Check
require_login();

// Guard against non-POST requests to prevent CSRF via GET links
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method for post deletion.');
    redirect(BASE_URL . '/index.php');
}

// 1. Verify CSRF Token (Task-4)
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security token invalid or expired. Deletion canceled.');
    redirect(BASE_URL . '/index.php');
}

$postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$postId) {
    set_flash('danger', 'Invalid post identifier.');
    redirect(BASE_URL . '/index.php');
}

try {
    $pdo = getDBConnection();

    // Fetch post to check permissions
    $stmt = $pdo->prepare("SELECT id, user_id, title FROM posts WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $postId]);
    $post = $stmt->fetch();

    if (!$post) {
        set_flash('warning', 'The post you are trying to delete does not exist.');
        redirect(BASE_URL . '/index.php');
    }

    // 2. Authorization Check (Task-4: RBAC)
    // Only the author or an admin can delete posts
    if (!can_delete_post($post)) {
        set_flash('danger', 'Access denied. You do not have permission to delete this post.');
        redirect(BASE_URL . '/index.php');
    }

    // 3. Execute Delete using Prepared Statement (Task-4)
    $deleteStmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
    $deleteStmt->execute([':id' => $postId]);

    set_flash('success', 'The post "' . e($post['title']) . '" was successfully deleted.');

} catch (PDOException $e) {
    set_flash('danger', 'Database error during deletion: ' . $e->getMessage());
}

// Redirect back to referring page or homepage
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php');
if (strpos($redirectUrl, 'view.php') !== false) {
    $redirectUrl = BASE_URL . '/index.php';
}
redirect($redirectUrl);
