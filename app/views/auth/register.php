<?php
// Sign-up page for new requesters (a complete page, no sidebar). Variables: $form, $errors.
$hasError  = fn(string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
$errorText = fn(string $field): string => isset($errors[$field])
    ? '<div class="invalid-feedback">' . e($errors[$field]) . '</div>'
    : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create an account | <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="card auth-card auth-card-wide">
        <div class="auth-card-top"></div>
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="brand-mark"><i class="bi bi-person-plus"></i></div>
                <h1 class="h4 mt-3 mb-1">Create an account</h1>
                <p class="text-muted small mb-0">Sign up to submit and track maintenance requests</p>
            </div>

            <?php require APP_PATH . '/views/layouts/alerts.php'; ?>

            <form method="post" action="<?= url('/register') ?>" class="row g-3">
                <?= csrf_field() ?>

                <div class="col-12">
                    <label for="full_name" class="form-label required">Full name</label>
                    <input type="text" class="form-control<?= $hasError('full_name') ?>" id="full_name" name="full_name"
                           value="<?= e($form['full_name']) ?>" maxlength="100" required autofocus autocomplete="name">
                    <?= $errorText('full_name') ?>
                </div>

                <div class="col-12">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" class="form-control<?= $hasError('email') ?>" id="email" name="email"
                           value="<?= e($form['email']) ?>" maxlength="150" required autocomplete="email">
                    <?= $errorText('email') ?>
                </div>

                <div class="col-sm-6">
                    <label for="contact_number" class="form-label">Contact number</label>
                    <input type="tel" class="form-control<?= $hasError('contact_number') ?>" id="contact_number" name="contact_number"
                           value="<?= e($form['contact_number']) ?>" maxlength="20" placeholder="09171234567" autocomplete="tel">
                    <?= $errorText('contact_number') ?>
                </div>

                <div class="col-sm-6">
                    <label for="department" class="form-label">Department / office</label>
                    <input type="text" class="form-control<?= $hasError('department') ?>" id="department" name="department"
                           value="<?= e($form['department']) ?>" maxlength="100" autocomplete="organization">
                    <?= $errorText('department') ?>
                </div>

                <div class="col-sm-6">
                    <label for="password" class="form-label required">Password</label>
                    <div class="input-group has-validation">
                        <input type="password" class="form-control<?= $hasError('password') ?>" id="password" name="password"
                               minlength="<?= User::MIN_PASSWORD ?>" maxlength="<?= User::MAX_PASSWORD ?>" required autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Show or hide password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <?= $errorText('password') ?>
                    </div>
                </div>

                <div class="col-sm-6">
                    <label for="confirm_password" class="form-label required">Confirm password</label>
                    <div class="input-group has-validation">
                        <input type="password" class="form-control<?= $hasError('confirm_password') ?>" id="confirm_password" name="confirm_password"
                               maxlength="<?= User::MAX_PASSWORD ?>" required autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Show or hide password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <?= $errorText('confirm_password') ?>
                    </div>
                </div>

                <div class="col-12 form-text mt-1">
                    At least <?= User::MIN_PASSWORD ?> characters, with at least one letter and one number.
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-plus me-1"></i>Create account
                    </button>
                </div>
            </form>

            <p class="text-center small text-muted mt-4 mb-0">
                Already have an account? <a href="<?= url('/login') ?>">Log in</a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/script.js') ?>"></script>
</body>
</html>
