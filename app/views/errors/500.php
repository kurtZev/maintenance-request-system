<?php
// Shown when an unexpected error happens (see set_exception_handler in bootstrap.php).
// The real error is written to the PHP error log, never shown here.
// Kept self-contained: it must work even when the database or session is unavailable.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something went wrong | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(BASE_URL . '/assets/css/style.css', ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <script>
        (function() {
            const saved = localStorage.getItem('mrs-theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = saved ? saved : (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
<div class="auth-wrapper position-relative">
    <div class="auth-theme-toggle">
        <button class="theme-toggle-btn shadow-sm" id="themeToggle" type="button" aria-label="Toggle theme" title="Toggle light/dark mode">
            <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
        </button>
    </div>
    <div class="container py-5">
        <div class="text-center mx-auto" style="max-width: 480px;">
            <div class="brand-mark mx-auto mb-3"><i class="bi bi-exclamation-triangle"></i></div>
            <h1 class="h3">Something went wrong</h1>
            <p class="text-muted">
                The page could not be shown because of an unexpected error.
                Please try again. If it keeps happening, contact the administrator.
            </p>
            <a href="<?= htmlspecialchars(BASE_URL . '/', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Go to home</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars(BASE_URL . '/assets/js/script.js', ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
