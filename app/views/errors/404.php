<?php
// Shown by the router when no route matches the address.
$home = is_logged_in() ? url(dashboard_path($_SESSION['role'])) : url('/login');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found | <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>?v=2" rel="stylesheet">
    <script>
        (function() {
            const saved = localStorage.getItem('mrs-theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = saved ? saved : (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
        })();

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('mrs-theme', next);
            updateThemeIcons(next);
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: next } }));
        }

        function updateThemeIcons(theme) {
            const icon = document.getElementById('themeToggleIcon');
            const btn = document.getElementById('themeToggle');
            if (icon) {
                if (theme === 'dark') {
                    icon.className = 'bi bi-sun-fill text-warning';
                } else {
                    icon.className = 'bi bi-moon-stars-fill text-primary';
                }
            }
            if (btn) {
                const label = theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
                btn.setAttribute('title', label);
                btn.setAttribute('aria-label', label);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
            updateThemeIcons(current);
        });
    </script>
</head>
<body>
<div class="auth-wrapper position-relative">
    <div class="auth-theme-toggle" style="position: fixed; top: 1.25rem; right: 1.25rem; z-index: 1050;">
        <button class="theme-toggle-btn shadow-sm" id="themeToggle" type="button" onclick="toggleTheme()" aria-label="Toggle theme" title="Toggle light/dark mode">
            <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
        </button>
    </div>
    <div class="text-center">
        <div class="brand-mark mx-auto mb-3"><i class="bi bi-signpost-split"></i></div>
        <h1 class="h3">Page not found</h1>
        <p class="text-muted">The page you are looking for does not exist or has moved.</p>
        <a href="<?= $home ?>" class="btn btn-primary"><i class="bi bi-house-door me-1"></i>Go to home</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/script.js') ?>"></script>
</body>
</html>
