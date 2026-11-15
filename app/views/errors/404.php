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
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="text-center">
        <div class="brand-mark mx-auto mb-3"><i class="bi bi-signpost-split"></i></div>
        <h1 class="h3">Page not found</h1>
        <p class="text-muted">The page you are looking for does not exist or has moved.</p>
        <a href="<?= $home ?>" class="btn btn-primary"><i class="bi bi-house-door me-1"></i>Go to home</a>
    </div>
</div>
</body>
</html>
