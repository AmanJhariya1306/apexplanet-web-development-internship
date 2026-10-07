<?php
/**
 * ApexPlanet Web Development Internship - Final Project
 * Flash Message / Alert Notification Partial
 */

require_once __DIR__ . '/../config/functions.php';

$flash = get_flash();
?>
<?php if ($flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
            <div class="me-2 fs-5">
                <?php if ($flash['type'] === 'success'): ?>
                    <i class="bi bi-check-circle-fill text-success"></i>
                <?php elseif ($flash['type'] === 'danger'): ?>
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                <?php elseif ($flash['type'] === 'warning'): ?>
                    <i class="bi bi-exclamation-circle-fill text-warning"></i>
                <?php else: ?>
                    <i class="bi bi-info-circle-fill text-info"></i>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1">
                <?= e($flash['message']) ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>
