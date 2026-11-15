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
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="text-center mx-auto" style="max-width: 480px;">
        <h1 class="h3">Something went wrong</h1>
        <p class="text-muted">
            The page could not be shown because of an unexpected error.
            Please try again. If it keeps happening, contact the administrator.
        </p>
        <a href="<?= htmlspecialchars(BASE_URL . '/', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Go to home</a>
    </div>
</div>
</body>
</html>
