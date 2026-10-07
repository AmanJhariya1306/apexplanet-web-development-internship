<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * Site Footer Partial
 */

require_once __DIR__ . '/../config/config.php';
?>
</main>

<footer class="mt-auto">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="brand-icon text-white bg-primary p-2 rounded-3"><i class="bi bi-code-slash fs-5"></i></span>
                    <span class="fs-4 fw-bold text-white">Apex<span style="color: #2dd4bf;">Blog</span></span>
                </div>
                <p class="text-secondary small pe-lg-4">
                    A secure, full-featured dynamic Blog CMS built with procedural PHP 8, MySQL, and Bootstrap 5. Developed for the 7th Semester B.Tech / BCA Internship Capstone at <strong>ApexPlanet Software Pvt Ltd</strong>.
                </p>
                <div class="d-flex gap-3">
                    <span class="badge bg-secondary">PHP 8.x</span>
                    <span class="badge bg-secondary">MySQL 8.x / MariaDB</span>
                    <span class="badge bg-secondary">PDO Prepared Statements</span>
                    <span class="badge bg-secondary">Bootstrap 5</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <h6 class="text-white fw-bold mb-3">Internship Tasks</h6>
                <ul class="list-unstyled small text-secondary">
                    <li class="mb-2"><span class="text-light">Task 1:</span> Dev Environment & Git</li>
                    <li class="mb-2"><span class="text-light">Task 2:</span> Database & CRUD System</li>
                    <li class="mb-2"><span class="text-light">Task 3:</span> Search, Pagination & UI</li>
                    <li class="mb-2"><span class="text-light">Task 4:</span> Security & RBAC Roles</li>
                    <li class="mb-2"><span class="text-light">Task 5:</span> Integrated Final Project</li>
                </ul>
            </div>

            <div class="col-6 col-lg-4">
                <h6 class="text-white fw-bold mb-3">Project Administration</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/setup/install.php" class="text-decoration-none">
                            <i class="bi bi-tools text-warning me-1"></i> Database Setup & Seed Wizard
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/auth/login.php" class="text-decoration-none">
                            <i class="bi bi-box-arrow-in-right text-info me-1"></i> Staff & User Login
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">
                            <i class="bi bi-collection text-success me-1"></i> Browse Published Articles
                        </a>
                    </li>
                </ul>
                <div class="p-3 rounded-3 bg-dark border border-secondary mt-3">
                    <div class="text-xs text-secondary mb-1">Company Contact Info (from PPT):</div>
                    <div class="small text-light"><i class="bi bi-envelope me-1"></i> info@apexplanet.in</div>
                    <div class="small text-light"><i class="bi bi-telephone me-1"></i> +91 9905879870</div>
                </div>
            </div>
        </div>

        <div class="border-top border-secondary pt-3 mt-4 text-center text-secondary small">
            <p class="mb-0">
                &copy; <?= date('Y') ?> ApexPlanet Software Pvt Ltd Internship Program. 7th Semester Final Project. Built with clean PHP & MySQL.
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3.3 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Application JavaScript -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
