<?php
// Admin dashboard. Variables from DashboardController::admin():
//   $cards      list of [label, value, color class, icon, link, note or null]
//   $total      number of requests (for the share bars)
//   $recent     newest requests
//   $byCategory rows of [label, total, open, completed, closed_other, avg_hours]
//   $byPriority same shape as $byCategory
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-0">Welcome, <?= e($_SESSION['full_name']) ?></h1>
        <p class="text-muted mb-0">Overview of all maintenance requests</p>
    </div>
    <a href="<?= url('/admin/requests?status=Pending') ?>" class="btn btn-primary">
        <i class="bi bi-hourglass-split me-1"></i>Review pending requests
    </a>
</div>

<!-- Statistic cards: each opens the matching filtered list -->
<div class="row g-3 mb-4">
    <?php foreach ($cards as [$label, $value, $color, $icon, $link, $note]): ?>
        <div class="col-xxl col-lg-4 col-sm-6">
            <a href="<?= url($link) ?>" class="stat-card stat-card-link">
                <div class="stat-icon <?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
                <div>
                    <div class="stat-value"><?= (int) $value ?></div>
                    <div class="stat-label"><?= e($label) ?></div>
                    <?php if ($note !== null): ?>
                        <div class="small text-muted"><?= e($note) ?></div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Recent requests -->
    <div class="col-xxl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-1"></i>Recent requests</span>
                <a href="<?= url('/admin/requests') ?>" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th style="min-width: 200px;">Request</th>
                            <th>Requester</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$recent): ?>
                        <tr>
                            <td colspan="6" class="table-empty">
                                <i class="bi bi-inbox d-block fs-2 mb-2"></i>No maintenance requests yet.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($recent as $row): ?>
                        <tr>
                            <td class="fw-semibold text-nowrap">
                                <a href="<?= url('/admin/requests/view?id=' . (int) $row['request_id']) ?>" class="text-decoration-none">
                                    <?= e($row['reference_no']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-medium"><?= e($row['title']) ?></div>
                                <div class="small text-muted"><?= e($row['category']) ?> &middot; <?= e($row['location']) ?></div>
                            </td>
                            <td><?= e($row['requester_name']) ?></td>
                            <td><?= priority_badge($row['priority']) ?></td>
                            <td><?= status_badge($row['status']) ?></td>
                            <td class="text-nowrap small"><?= format_date($row['date_submitted'], false) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Category and priority statistics -->
    <div class="col-xxl-4">
        <div class="row g-4">
            <div class="col-xxl-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-tags me-1"></i>By category</div>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Open</th>
                                    <th style="width: 35%;">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($byCategory as $row): ?>
                                <?php $share = $total > 0 ? round($row['total'] / $total * 100) : 0; ?>
                                <tr class="<?= $row['total'] === 0 ? 'text-muted' : '' ?>">
                                    <td>
                                        <a href="<?= url('/admin/requests?category=' . rawurlencode($row['label'])) ?>" class="text-reset text-decoration-none">
                                            <?= e($row['label']) ?>
                                        </a>
                                    </td>
                                    <td class="text-end fw-semibold"><?= $row['total'] ?></td>
                                    <td class="text-end"><?= $row['open'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="share-bar flex-grow-1"><span style="width: <?= $share ?>%"></span></div>
                                            <span class="small text-muted"><?= $share ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xxl-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-flag me-1"></i>By priority</div>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Priority</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Open</th>
                                    <th style="width: 35%;">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach (array_reverse($byPriority) as $row): ?>
                                <?php $share = $total > 0 ? round($row['total'] / $total * 100) : 0; ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('/admin/requests?priority=' . rawurlencode($row['label'])) ?>" class="text-decoration-none">
                                            <?= priority_badge($row['label']) ?>
                                        </a>
                                    </td>
                                    <td class="text-end fw-semibold"><?= $row['total'] ?></td>
                                    <td class="text-end"><?= $row['open'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="share-bar flex-grow-1"><span style="width: <?= $share ?>%"></span></div>
                                            <span class="small text-muted"><?= $share ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
