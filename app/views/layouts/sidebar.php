<?php
// Sidebar navigation, loaded by layouts/header.php.
// Each role has its own list:  'key' => [route, Bootstrap icon, label]
// The key is what a controller passes as 'activePage' to highlight its link.
$menus = [
    'admin' => [
        'dashboard' => ['/admin/dashboard', 'bi-speedometer2',    'Dashboard'],
        'requests'  => ['/admin/requests',  'bi-clipboard-check', 'All Requests'],
        'users'     => ['/admin/users',     'bi-people',          'Users'],
        'reports'   => ['/admin/reports',   'bi-bar-chart-line',  'Reports'],
    ],
    'requester' => [
        'dashboard'   => ['/requester/dashboard',       'bi-speedometer2', 'Dashboard'],
        'add_request' => ['/requester/requests/create', 'bi-plus-square',  'New Request'],
        'my_requests' => ['/requester/requests',        'bi-card-list',    'My Requests'],
    ],
];

// Show a link only when its route exists in routes/web.php, so the menu never
// leads to a "Not Found" page. Links appear on their own as features are built.
$menu = array_filter($menus[$_SESSION['role']] ?? [], fn($item) => route_exists($item[0]));
?>
<aside class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="sidebar-label">Menu</div>
    <ul class="sidebar-nav">
        <?php foreach ($menu as $key => [$path, $icon, $label]): ?>
            <li>
                <a class="sidebar-link <?= $activePage === $key ? 'active' : '' ?>"
                   href="<?= url($path) ?>"
                   <?= $activePage === $key ? 'aria-current="page"' : '' ?>>
                    <span class="sidebar-icon"><i class="bi <?= $icon ?>"></i></span>
                    <?= e($label) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
