<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * Main Blog Homepage: Listing, Search & Pagination
 * 
 * Task Reference:
 * - Task-2: Read: Display a list of posts from the database
 * - Task-3: Search Functionality, Pagination & Responsive Bootstrap UI
 * - Task-4: PDO Prepared Statements, RBAC Actions, Sanitization
 * - Task-5: Full System Integration
 */

$pageTitle = 'Home - Technical Articles & Insights';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

$pdo = getDBConnection();
$currentUser = current_user();
$categories = get_categories();

// Query Parameters for Search, Filter & Pagination
$searchQuery = trim($_GET['q'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$authorFilter = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = ($page && $page > 0) ? $page : 1;
$perPage = POSTS_PER_PAGE;

// Build WHERE Clause dynamically with Prepared Statements (Task-4: Security)
$whereClauses = [];
$params = [];

// Only show published posts to general public; authors can also see their own drafts
if (is_admin() || is_editor()) {
    // Admins and editors can see all posts or published by default
} elseif (is_logged_in()) {
    $whereClauses[] = "(posts.status = 'published' OR posts.user_id = :logged_user_id)";
    $params[':logged_user_id'] = $currentUser['id'];
} else {
    $whereClauses[] = "posts.status = 'published'";
}

// Search by Title or Content (Task-3: Search Functionality)
if ($searchQuery !== '') {
    $whereClauses[] = "(posts.title LIKE :search_title OR posts.content LIKE :search_content)";
    $params[':search_title'] = '%' . $searchQuery . '%';
    $params[':search_content'] = '%' . $searchQuery . '%';
}

// Filter by Category
if ($categoryFilter !== '' && in_array($categoryFilter, $categories)) {
    $whereClauses[] = "posts.category = :category";
    $params[':category'] = $categoryFilter;
}

// Filter by Author
if ($authorFilter) {
    $whereClauses[] = "posts.user_id = :author_id";
    $params[':author_id'] = $authorFilter;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// 1. Get Total Count for Pagination (Task-3: Pagination)
$countSql = "SELECT COUNT(*) FROM posts $whereSql";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val);
}
$countStmt->execute();
$totalPosts = (int)$countStmt->fetchColumn();

// Calculate Total Pages & Offset
$totalPages = max(1, (int)ceil($totalPosts / $perPage));
if ($page > $totalPages && $totalPosts > 0) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// 2. Fetch Paginated Records with Author Data
$postsSql = "
    SELECT posts.*, users.username, users.role AS author_role
    FROM posts
    JOIN users ON posts.user_id = users.id
    $whereSql
    ORDER BY posts.created_at DESC
    LIMIT :limit OFFSET :offset
";

$postsStmt = $pdo->prepare($postsSql);
foreach ($params as $key => $val) {
    $postsStmt->bindValue($key, $val);
}
$postsStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$postsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$postsStmt->execute();
$posts = $postsStmt->fetchAll();

// Helper to preserve active query parameters when generating pagination links
function build_page_url($pageNum, $q, $cat, $author) {
    $params = ['page' => $pageNum];
    if ($q !== '') $params['q'] = $q;
    if ($cat !== '') $params['category'] = $cat;
    if ($author) $params['user_id'] = $author;
    return '?' . http_build_query($params);
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner (Task-3: UI Improvements) -->
<section class="hero-section text-center">
    <div class="container py-4">
        <div class="hero-badge mb-3">
            <i class="bi bi-patch-check-fill text-warning"></i> ApexPlanet Software Pvt Ltd • 45-Day Internship Project
        </div>
        <h1 class="display-5 fw-bold mb-3 text-white">Explore Engineering & Web Technologies</h1>
        <p class="lead text-white-50 mx-auto mb-4" style="max-width: 650px;">
            A responsive, secure content management platform developed with PHP 8, MySQL, and modern security patterns.
        </p>

        <!-- Search Form (Task-3: Search Functionality) -->
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <form method="GET" action="<?= BASE_URL ?>/index.php" class="search-container">
                    <?php if ($categoryFilter !== ''): ?>
                        <input type="hidden" name="category" value="<?= e($categoryFilter) ?>">
                    <?php endif; ?>
                    <i class="bi bi-search text-muted ms-3 fs-5"></i>
                    <input type="text" name="q" class="form-control search-input" placeholder="Search articles by title or keyword..." value="<?= e($searchQuery) ?>">
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 py-2 me-1">
                        Search
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Main Feed Section -->
<div class="container py-5">
    <!-- Category Filter Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="text-secondary small fw-semibold me-1"><i class="bi bi-funnel"></i> Filter:</span>
            <a href="<?= BASE_URL ?>/index.php<?= ($searchQuery ? '?q=' . urlencode($searchQuery) : '') ?>" class="btn btn-sm <?= ($categoryFilter === '' && !$authorFilter) ? 'btn-primary-custom' : 'btn-outline-secondary' ?> rounded-pill px-3">
                All
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>/index.php?category=<?= urlencode($cat) ?><?= ($searchQuery ? '&q=' . urlencode($searchQuery) : '') ?>" class="btn btn-sm <?= ($categoryFilter === $cat) ? 'btn-primary-custom' : 'btn-outline-secondary' ?> rounded-pill px-3">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="text-muted small">
            Showing <strong class="text-dark"><?= $totalPosts ?></strong> <?= $totalPosts === 1 ? 'article' : 'articles' ?>
        </div>
    </div>

    <!-- Active Search Filters Notification -->
    <?php if ($searchQuery !== '' || $categoryFilter !== '' || $authorFilter): ?>
        <div class="alert alert-info py-2 px-3 small d-flex align-items-center justify-content-between mb-4 rounded-3 border-0">
            <div>
                <i class="bi bi-info-circle-fill me-1"></i> Active filter:
                <?php if ($searchQuery !== ''): ?>
                    Keyword: <strong>"<?= e($searchQuery) ?>"</strong>
                <?php endif; ?>
                <?php if ($categoryFilter !== ''): ?>
                    Category: <strong>"<?= e($categoryFilter) ?>"</strong>
                <?php endif; ?>
                <?php if ($authorFilter): ?>
                    Author ID: <strong>#<?= (int)$authorFilter ?></strong>
                <?php endif; ?>
            </div>
            <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none fw-semibold">
                <i class="bi bi-x-circle me-1"></i> Clear Filters
            </a>
        </div>
    <?php endif; ?>

    <!-- Posts Grid (Task-2: Read & Task-3: UI) -->
    <?php if (empty($posts)): ?>
        <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-white my-4">
            <div class="display-1 text-muted mb-3"><i class="bi bi-journal-x"></i></div>
            <h4 class="fw-bold text-dark">No Articles Found</h4>
            <p class="text-muted mx-auto" style="max-width: 450px;">
                We couldn't find any articles matching your search criteria. Try modifying your search keywords or resetting category filters.
            </p>
            <div class="mt-2">
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary rounded-pill px-4">View All Posts</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= BASE_URL ?>/posts/create.php" class="btn btn-primary-custom rounded-pill px-4 ms-2">Write First Post</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card post-card">
                        <div class="card-body p-4 d-flex flex-direction-column flex-grow-1">
                            <!-- Category Badge & Read Time -->
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="post-category-badge"><?= e($post['category']) ?></span>
                                <span class="text-muted small"><i class="bi bi-clock me-1"></i><?= read_time($post['content']) ?> min</span>
                            </div>

                            <!-- Post Title -->
                            <h5 class="post-card-title mb-2">
                                <a href="<?= BASE_URL ?>/posts/view.php?id=<?= (int)$post['id'] ?>" class="text-decoration-none text-dark">
                                    <?= e($post['title']) ?>
                                </a>
                            </h5>

                            <!-- Excerpt -->
                            <p class="post-card-excerpt mb-4 flex-grow-1">
                                <?= e(truncate_text($post['content'], 130)) ?>
                            </p>

                            <!-- Author Meta & Footer -->
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="post-author-avatar">
                                        <?= strtoupper(substr($post['username'], 0, 1)) ?>
                                    </span>
                                    <div>
                                        <div class="small fw-bold text-dark d-flex align-items-center gap-1">
                                            <span><?= e($post['username']) ?></span>
                                            <?php if ($post['author_role'] === 'admin'): ?>
                                                <span class="badge bg-danger" style="font-size: 0.55rem;">Admin</span>
                                            <?php elseif ($post['author_role'] === 'editor'): ?>
                                                <span class="badge bg-warning text-dark" style="font-size: 0.55rem;">Editor</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <?= time_ago($post['created_at']) ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Read More Action -->
                                <a href="<?= BASE_URL ?>/posts/view.php?id=<?= (int)$post['id'] ?>" class="btn btn-sm btn-outline-primary-custom rounded-pill px-3">
                                    Read <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>

                            <!-- Draft Badge if post is draft -->
                            <?php if ($post['status'] === 'draft'): ?>
                                <div class="mt-2 text-end">
                                    <span class="badge bg-secondary">Draft</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination Controls (Task-3: Pagination) -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-5">
                <ul class="pagination justify-content-center">
                    <!-- First & Previous -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= build_page_url(1, $searchQuery, $categoryFilter, $authorFilter) ?>" aria-label="First">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                    </li>
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= build_page_url($page - 1, $searchQuery, $categoryFilter, $authorFilter) ?>" aria-label="Previous">
                            <i class="bi bi-chevron-left"></i> Prev
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php
                    $range = 2;
                    for ($i = 1; $i <= $totalPages; $i++):
                        if ($i == 1 || $i == $totalPages || ($i >= $page - $range && $i <= $page + $range)):
                    ?>
                        <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= build_page_url($i, $searchQuery, $categoryFilter, $authorFilter) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php elseif ($i == $page - $range - 1 || $i == $page + $range + 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; endfor; ?>

                    <!-- Next & Last -->
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= build_page_url($page + 1, $searchQuery, $categoryFilter, $authorFilter) ?>" aria-label="Next">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= build_page_url($totalPages, $searchQuery, $categoryFilter, $authorFilter) ?>" aria-label="Last">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
