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
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
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
