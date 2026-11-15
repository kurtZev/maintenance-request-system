<?php
// All requests (admin). Variables: $requests, $total, $filters, $page, $totalPages.
$isFiltered = implode('', $filters) !== '';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">All Requests</h1>
        <p class="text-muted mb-0"><?= $total ?> request<?= $total === 1 ? '' : 's' ?><?= $isFiltered ? ' match your filters' : ' in total' ?></p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/requests') ?>" class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label for="q" class="form-label small">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" id="q" name="q" value="<?= e($filters['q']) ?>"
                           placeholder="Reference, title, location or requester" maxlength="100">
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <label for="status" class="form-label small">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All</option>
                    <?php foreach (MaintenanceRequest::STATUSES as $option): ?>
                        <option <?= $filters['status'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label for="category" class="form-label small">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All</option>
                    <?php foreach (MaintenanceRequest::CATEGORIES as $option): ?>
                        <option <?= $filters['category'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label for="priority" class="form-label small">Priority</label>
                <select class="form-select" id="priority" name="priority">
                    <option value="">All</option>
                    <?php foreach (MaintenanceRequest::PRIORITIES as $option): ?>
                        <option <?= $filters['priority'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label for="date_from" class="form-label small">Submitted from</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?= e($filters['date_from']) ?>">
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label for="date_to" class="form-label small">Submitted to</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?= e($filters['date_to']) ?>">
            </div>
            <div class="col-lg-auto col-md-8 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                <?php if ($isFiltered): ?>
                    <a href="<?= url('/admin/requests') ?>" class="btn btn-light border">Reset</a>
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
                    <th>Requester</th>
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
                        <?= $isFiltered ? 'No requests match your filters.' : 'No maintenance requests yet.' ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($requests as $row): ?>
                <?php $viewUrl = url('/admin/requests/view?id=' . (int) $row['request_id']); ?>
                <tr>
                    <td class="fw-semibold text-nowrap"><a href="<?= $viewUrl ?>" class="text-decoration-none"><?= e($row['reference_no']) ?></a></td>
                    <td>
                        <div class="fw-medium"><?= e($row['title']) ?></div>
                        <div class="small text-muted"><?= e($row['category']) ?> &middot; <?= e($row['location']) ?></div>
                    </td>
                    <td><?= e($row['requester_name']) ?></td>
                    <td><?= priority_badge($row['priority']) ?></td>
                    <td><?= status_badge($row['status']) ?></td>
                    <td class="text-nowrap small"><?= format_date($row['date_submitted'], false) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= $viewUrl ?>" class="btn btn-sm btn-outline-primary" title="View" aria-label="View <?= e($row['reference_no']) ?>">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= url('/admin/requests/edit?id=' . (int) $row['request_id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit" aria-label="Edit <?= e($row['reference_no']) ?>">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="post" action="<?= url('/admin/requests/delete') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $row['request_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                    aria-label="Delete <?= e($row['reference_no']) ?>"
                                    data-confirm="Delete request <?= e($row['reference_no']) ?>? This cannot be undone.">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php $basePath = '/admin/requests'; require APP_PATH . '/views/partials/pagination.php'; ?>
</div>
