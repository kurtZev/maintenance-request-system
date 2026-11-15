<?php
// The requester's own requests. Variables: $requests, $total, $filters, $page, $totalPages.
$isFiltered = $filters['q'] !== '' || $filters['status'] !== '';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">My Requests</h1>
        <p class="text-muted mb-0"><?= $total ?> request<?= $total === 1 ? '' : 's' ?><?= $isFiltered ? ' match your search' : '' ?></p>
    </div>
    <a href="<?= url('/requester/requests/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Request
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?= url('/requester/requests') ?>" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label for="q" class="form-label small">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" id="q" name="q" value="<?= e($filters['q']) ?>"
                           placeholder="Reference, title or location" maxlength="100">
                </div>
            </div>
            <div class="col-md-3 col-7">
                <label for="status" class="form-label small">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All</option>
                    <?php foreach (MaintenanceRequest::STATUSES as $option): ?>
                        <option <?= $filters['status'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                <?php if ($isFiltered): ?>
                    <a href="<?= url('/requester/requests') ?>" class="btn btn-light border">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th style="min-width: 220px;">Request</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$requests): ?>
                <tr>
                    <td colspan="7" class="table-empty">
                        <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                        <?php if ($isFiltered): ?>
                            No requests match your search.
                        <?php else: ?>
                            You have not submitted any requests yet.
                            <a href="<?= url('/requester/requests/create') ?>">Submit your first one</a>.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($requests as $row): ?>
                <?php
                $viewUrl   = url('/requester/requests/view?id=' . (int) $row['request_id']);
                $isPending = $row['status'] === 'Pending';
                ?>
                <tr>
                    <td class="fw-semibold text-nowrap"><a href="<?= $viewUrl ?>" class="text-decoration-none"><?= e($row['reference_no']) ?></a></td>
                    <td>
                        <div class="fw-medium"><?= e($row['title']) ?></div>
                        <div class="small text-muted"><?= e($row['location']) ?></div>
                    </td>
                    <td><?= e($row['category']) ?></td>
                    <td><?= priority_badge($row['priority']) ?></td>
                    <td><?= status_badge($row['status']) ?></td>
                    <td class="text-nowrap small"><?= format_date($row['date_submitted'], false) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= $viewUrl ?>" class="btn btn-sm btn-outline-primary" title="View" aria-label="View <?= e($row['reference_no']) ?>">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php if ($isPending): ?>
                            <a href="<?= url('/requester/requests/edit?id=' . (int) $row['request_id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit" aria-label="Edit <?= e($row['reference_no']) ?>">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="post" action="<?= url('/requester/requests/cancel') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $row['request_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel request"
                                        aria-label="Cancel <?= e($row['reference_no']) ?>"
                                        data-confirm="Cancel request <?= e($row['reference_no']) ?>?">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php $basePath = '/requester/requests'; require APP_PATH . '/views/partials/pagination.php'; ?>
</div>
