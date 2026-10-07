/**
 * ApexPlanet Web Development Internship - Final Project
 * Client-Side JavaScript Interactivity & Validation (Task-4: Form Validation)
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // 1. Client-side Form Validation (Bootstrap 5)
    // Applies custom Bootstrap validation styles to forms marked with .needs-validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // 2. Auto-fade Dismissible Alerts after 6 seconds
    const autoDismissAlerts = document.querySelectorAll('.alert-dismissible');
    autoDismissAlerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });

    // 3. Post Delete Confirmation Handler
    const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
    deleteButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const postTitle = btn.getAttribute('data-post-title') || 'this post';
            if (!confirm(`Are you sure you want to permanently delete "${postTitle}"?\nThis action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    });

    // 4. Quick-Fill Demo Credentials Helper on Login Form
    const demoFillButtons = document.querySelectorAll('.btn-demo-fill');
    demoFillButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const usernameInput = document.getElementById('username_or_email');
            const passwordInput = document.getElementById('password');
            if (usernameInput && passwordInput) {
                usernameInput.value = btn.getAttribute('data-user');
                passwordInput.value = btn.getAttribute('data-pass');
                // Trigger visual feedback
                usernameInput.classList.add('is-valid');
                passwordInput.classList.add('is-valid');
                setTimeout(() => {
                    usernameInput.classList.remove('is-valid');
                    passwordInput.classList.remove('is-valid');
                }, 1500);
            }
        });
    });

    // 5. Password Confirmation Validator on Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const password = document.getElementById('reg_password');
        const confirmPassword = document.getElementById('reg_confirm_password');

        function validatePasswordMatch() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords do not match");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        if (password && confirmPassword) {
            password.addEventListener('change', validatePasswordMatch);
            confirmPassword.addEventListener('keyup', validatePasswordMatch);
        }
    }
});
