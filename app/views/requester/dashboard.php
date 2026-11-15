<?php
// Requester dashboard. Variables: $cards = list of [label, value, color class, icon].
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h3 mb-0">Welcome, <?= e($_SESSION['full_name']) ?></h1>
    <a href="<?= url('/requester/requests/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Request
    </a>
</div>

<div class="row g-3">
    <?php foreach ($cards as [$label, $value, $color, $icon]): ?>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon <?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
                <div>
                    <div class="stat-value"><?= (int) $value ?></div>
                    <div class="stat-label"><?= e($label) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
