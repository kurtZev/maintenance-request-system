<?php
// Login page (a complete page, no sidebar). Variables: $email, $error.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in | <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>?v=2" rel="stylesheet">
    <style>
        .auth-theme-toggle {
            position: fixed !important;
            top: 1.25rem !important;
            right: 1.25rem !important;
            z-index: 1050 !important;
        }
        .theme-toggle-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 42px !important;
            height: 42px !important;
            border: 1px solid var(--mrs-border, #cbd5e1) !important;
            border-radius: 0.65rem !important;
            background: var(--mrs-surface, #ffffff) !important;
            color: var(--mrs-text, #1e293b) !important;
            font-size: 1.25rem !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }
        .theme-toggle-btn:hover {
            transform: scale(1.08) !important;
        }
        [data-bs-theme="dark"] body {
            background-color: #0b1329 !important;
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .auth-wrapper {
            background: linear-gradient(160deg, #060a14 0%, #0b1329 60%) !important;
        }
        [data-bs-theme="dark"] .auth-card {
            background-color: #131e36 !important;
            border-color: #223254 !important;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.5) !important;
        }
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .input-group-text {
            background-color: #0d172e !important;
            border-color: #223254 !important;
            color: #e2e8f0 !important;
        }
        [data-bs-theme="dark"] .theme-toggle-btn {
            background: #131e36 !important;
            border-color: #223254 !important;
            color: #e2e8f0 !important;
        }
    </style>
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
    <div class="auth-theme-toggle">
        <button class="theme-toggle-btn shadow-sm" id="themeToggle" type="button" onclick="toggleTheme()" aria-label="Toggle theme" title="Toggle light/dark mode">
            <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
        </button>
    </div>
    <div class="card auth-card">
        <div class="auth-card-top"></div>
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="brand-mark"><i class="bi bi-tools"></i></div>
                <h1 class="h4 mt-3 mb-1"><?= e(APP_NAME) ?></h1>
                <p class="text-muted small mb-0">Log in to your account</p>
            </div>

            <?php require APP_PATH . '/views/layouts/alerts.php'; ?>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('/login') ?>" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($email) ?>" maxlength="150" required autofocus
                               autocomplete="username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               required autocomplete="current-password">
                        <button type="button" class="btn btn-outline-secondary password-toggle"
                                aria-label="Show or hide password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Log in
                </button>
            </form>

            <p class="text-center small text-muted mt-4 mb-0">
                No account yet? <a href="<?= url('/register') ?>">Create one</a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/script.js') ?>"></script>
</body>
</html>
