<?php
// One-time messages saved with set_flash(). Shown once, then cleared.
foreach (take_flash() as $flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible flash-message" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endforeach; ?>
