<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * Core Helper & Security Functions
 * 
 * Task Reference:
 * - Task-2: User Authentication & Sessions
 * - Task-3: UI & Formatting Helpers
 * - Task-4: Security Enhancements (XSS Prevention, CSRF Protection, RBAC, Validation)
 */

require_once __DIR__ . '/config.php';

// ==========================================
// 1. SECURITY & SANITIZATION (Task-4)
// ==========================================

/**
 * Escapes HTML characters to prevent Cross-Site Scripting (XSS).
 *
 * @param string|null $string
 * @return string
 */
function e(?string $string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generates or retrieves existing CSRF token for the current session.
 *
 * @return string
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Outputs a hidden input field containing the CSRF token.
 *
 * @return string
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Validates the submitted CSRF token using constant-time comparison.
 *
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Basic string sanitization.
 *
 * @param string $data
 * @return string
 */
function sanitize_input(string $data): string {
    return trim(strip_tags($data));
}

// ==========================================
// 2. AUTHENTICATION & RBAC (Task-2 & Task-4)
// ==========================================

/**
 * Checks if a user is currently logged in.
 *
 * @return bool
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Returns the currently authenticated user record from session.
 *
 * @return array|null
 */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Checks if the current user is an Admin.
 *
 * @return bool
 */
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

/**
 * Checks if the current user is an Editor or Admin.
 *
 * @return bool
 */
function is_editor(): bool {
    if (!is_logged_in()) return false;
    $role = $_SESSION['user']['role'] ?? '';
    return ($role === 'editor' || $role === 'admin');
}

/**
 * Checks if a user has permission to edit a specific post.
 * Rule: Authors can edit their own posts; Editors and Admins can edit any post.
 *
 * @param array $post
 * @param array|null $user
 * @return bool
 */
function can_edit_post(array $post, ?array $user = null): bool {
    $user = $user ?? current_user();
    if (!$user) return false;
    if ($user['role'] === 'admin' || $user['role'] === 'editor') return true;
    return (int)$post['user_id'] === (int)$user['id'];
}

/**
 * Checks if a user has permission to delete a specific post.
 * Rule: Authors can delete their own posts; Admins can delete any post.
 *
 * @param array $post
 * @param array|null $user
 * @return bool
 */
function can_delete_post(array $post, ?array $user = null): bool {
    $user = $user ?? current_user();
    if (!$user) return false;
    if ($user['role'] === 'admin') return true;
    return (int)$post['user_id'] === (int)$user['id'];
}

/**
 * Middleware: Requires the user to be logged in.
 */
function require_login(): void {
    if (!is_logged_in()) {
        set_flash('danger', 'You must log in to access this page.');
        redirect(BASE_URL . '/auth/login.php');
    }
}

/**
 * Middleware: Requires the user to have the Admin role.
 */
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'Access denied. Administrator privileges required.');
        redirect(BASE_URL . '/index.php');
    }
}

/**
 * Middleware: Requires the user to be Editor or Admin.
 */
function require_editor_or_admin(): void {
    require_login();
    if (!is_editor()) {
        set_flash('danger', 'Access denied. Editor or Administrator privileges required.');
        redirect(BASE_URL . '/index.php');
    }
}

// ==========================================
// 3. FLASH MESSAGING & REDIRECTION
// ==========================================

/**
 * Sets a flash message for the next request.
 *
 * @param string $type ('success', 'danger', 'warning', 'info')
 * @param string $message
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Retrieves and clears the flash message.
 *
 * @return array|null
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Safely redirects the browser to a given URL.
 *
 * @param string $url
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

// ==========================================
// 4. UI & CONTENT HELPERS (Task-3)
// ==========================================

/**
 * Truncates text to a specified character limit without breaking words.
 *
 * @param string $text
 * @param int $limit
 * @param string $end
 * @return string
 */
function truncate_text(string $text, int $limit = 140, string $end = '...'): string {
    $cleanText = strip_tags($text);
    if (mb_strlen($cleanText) <= $limit) {
        return $cleanText;
    }
    $truncated = mb_substr($cleanText, 0, $limit);
    $lastSpace = mb_strrpos($truncated, ' ');
    if ($lastSpace !== false) {
        $truncated = mb_substr($truncated, 0, $lastSpace);
    }
    return $truncated . $end;
}

/**
 * Calculates human-readable time ago or formatted date.
 *
 * @param string $datetime
 * @return string
 */
function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = round($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = round($diff / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = round($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $time);
    }
}

/**
 * Calculates estimated read time in minutes.
 *
 * @param string $content
 * @return int
 */
function read_time(string $content): int {
    $wordCount = str_word_count(strip_tags($content));
    $minutes = ceil($wordCount / 200);
    return max(1, (int)$minutes);
}

/**
 * Returns available blog categories.
 *
 * @return array
 */
function get_categories(): array {
    return [
        'Web Development',
        'PHP & MySQL',
        'Software Engineering',
        'Security & DevOps',
        'Internship Experience'
    ];
}
