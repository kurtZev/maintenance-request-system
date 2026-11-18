<?php
// Top of every logged-in page: <head>, top navbar, sidebar, breadcrumb bar.
// Loaded by layouts/main.php; uses $pageTitle and $activePage.
$homeUrl = url(dashboard_path($_SESSION['role']));

// Initials for the round avatar, e.g. "Maria Santos" -> "MS".
$nameParts = preg_split('/\s+/', trim($_SESSION['full_name']));
$initials  = strtoupper(substr($nameParts[0], 0, 1) . (count($nameParts) > 1 ? substr(end($nameParts), 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
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

<!-- Top navbar -->
<header class="topbar">
    <button class="topbar-toggle d-lg-none" id="sidebarToggle" type="button" aria-label="Open menu">
        <i class="bi bi-list"></i>
    </button>

    <a class="topbar-brand" href="<?= $homeUrl ?>">
        <span class="brand-mark"><i class="bi bi-tools"></i></span>
        <span class="d-none d-sm-inline"><?= e(APP_NAME) ?></span>
    </a>

    <div class="d-flex align-items-center gap-2 ms-auto">
        <button class="theme-toggle-btn" id="themeToggle" type="button" onclick="toggleTheme()" aria-label="Toggle theme" title="Toggle light/dark mode">
            <i class="bi bi-moon-stars" id="themeToggleIcon"></i>
        </button>

        <div class="dropdown">
            <button class="user-menu dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="user-avatar"><?= e($initials) ?></span>
                <span class="d-none d-md-block">
                    <span class="user-name d-block"><?= e($_SESSION['full_name']) ?></span>
                    <span class="user-role d-block"><?= e(ucfirst($_SESSION['role'])) ?></span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm">
                <div class="dropdown-header d-md-none">
                    <?= e($_SESSION['full_name']) ?> (<?= e(ucfirst($_SESSION['role'])) ?>)
                </div>
                <form method="post" action="<?= url('/logout') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<?php require __DIR__ . '/sidebar.php'; ?>

<div class="app-main">
    <!-- Breadcrumb bar -->
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= $homeUrl ?>"><i class="bi bi-house-door me-1"></i>Home</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($pageTitle) ?></li>
            </ol>
        </nav>
        <span class="small text-muted d-none d-sm-inline">
            <i class="bi bi-calendar3 me-1"></i><?= date('F j, Y') ?>
        </span>
    </div>

    <main class="page-content">
        <?php require __DIR__ . '/alerts.php'; ?>
