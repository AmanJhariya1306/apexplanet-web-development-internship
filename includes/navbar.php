<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * Navigation Bar Partial (Task-3: UI Improvements & Task-4: RBAC Navigation)
 */

$currentUser = current_user();
$categories = get_categories();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-2 py-lg-3 shadow-xs">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand navbar-brand-custom" href="<?= BASE_URL ?>/index.php">
            <span class="brand-icon"><i class="bi bi-code-slash"></i></span>
            <span>Apex<span style="color: #14b8a6;">Blog</span></span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links & Actions -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['SCRIPT_NAME']) === 'index.php' && empty($_GET['category'])) ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>

                <!-- Categories Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-grid me-1"></i> Categories
                    </a>
                    <ul class="dropdown-menu border-0 shadow-sm rounded-3" aria-labelledby="categoryDropdown">
                        <li><a class="dropdown-menu-item dropdown-item" href="<?= BASE_URL ?>/index.php">All Categories</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/index.php?category=<?= urlencode($cat) ?>">
                                    <?= e($cat) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <?php if (is_editor()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-primary fw-semibold" href="<?= BASE_URL ?>/admin/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Right-hand User Navigation -->
            <div class="d-flex align-items-center gap-2">
                <?php if (is_logged_in()): ?>
                    <!-- Write Post Button -->
                    <a href="<?= BASE_URL ?>/posts/create.php" class="btn btn-primary-custom btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-pencil-square"></i>
                        <span>New Post</span>
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light rounded-pill px-3 py-1 border d-flex align-items-center gap-2 dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="post-author-avatar" style="width: 26px; height: 26px; font-size: 0.75rem;">
                                <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                            </span>
                            <span class="fw-semibold small text-dark"><?= e($currentUser['username']) ?></span>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <span class="badge bg-danger" style="font-size: 0.65rem;">Admin</span>
                            <?php elseif ($currentUser['role'] === 'editor'): ?>
                                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Editor</span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark" style="font-size: 0.65rem;">Author</span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 py-2 mt-2" aria-labelledby="userMenu" style="min-width: 200px;">
                            <li class="px-3 py-2 border-bottom">
                                <div class="small text-muted">Signed in as</div>
                                <div class="fw-bold text-dark"><?= e($currentUser['email']) ?></div>
                            </li>
                            <?php if (is_admin()): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/dashboard.php">
                                        <i class="bi bi-shield-lock-fill text-danger me-2"></i> Admin Control Panel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/users.php">
                                        <i class="bi bi-people-fill text-primary me-2"></i> Manage Users
                                    </a>
                                </li>
                            <?php elseif (is_editor()): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/dashboard.php">
                                        <i class="bi bi-speedometer2 text-warning me-2"></i> Editor Dashboard
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item py-2" href="<?= BASE_URL ?>/index.php?user_id=<?= (int)$currentUser['id'] ?>">
                                    <i class="bi bi-journal-text text-secondary me-2"></i> My Published Posts
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>/auth/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                </a>
                            </li>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- Guest Actions -->
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                    </a>
                    <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary-custom btn-sm rounded-pill px-3 py-2 shadow-sm">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
