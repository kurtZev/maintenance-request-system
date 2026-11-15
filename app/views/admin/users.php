<?php
// User management list (admin). Variables: $users, $total, $filters, $page, $totalPages.
$isFiltered = implode('', $filters) !== '';
$myId       = (int) $_SESSION['user_id'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Users</h1>
        <p class="text-muted mb-0"><?= $total ?> account<?= $total === 1 ? '' : 's' ?><?= $isFiltered ? ' match your filters' : ' in total' ?></p>
    </div>
    <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Add User
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/users') ?>" class="row g-2 align-items-end">
            <div class="col-lg-5 col-md-12">
                <label for="q" class="form-label small">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" id="q" name="q" value="<?= e($filters['q']) ?>"
                           placeholder="Name, email or department" maxlength="100">
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label for="role" class="form-label small">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="">All</option>
                    <?php foreach (User::ROLES as $option): ?>
                        <option value="<?= e($option) ?>" <?= $filters['role'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <label for="status" class="form-label small">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All</option>
                    <?php foreach (User::STATUSES as $option): ?>
                        <option value="<?= e($option) ?>" <?= $filters['status'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-auto col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                <?php if ($isFiltered): ?>
                    <a href="<?= url('/admin/users') ?>" class="btn btn-light border">Reset</a>
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
                    <th style="min-width: 220px;">Name</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th class="text-center">Requests</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$users): ?>
                <tr>
                    <td colspan="7" class="table-empty">
                        <i class="bi bi-people d-block fs-2 mb-2"></i>
                        <?= $isFiltered ? 'No users match your filters.' : 'No user accounts yet.' ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($users as $row): ?>
                <?php
                $id        = (int) $row['user_id'];
                $isSelf    = $id === $myId;
                $isActive  = $row['status'] === 'active';
                $canDelete = !$isSelf && (int) $row['request_count'] === 0;
                ?>
                <tr>
                    <td>
                        <div class="fw-medium">
                            <?= e($row['full_name']) ?>
                            <?php if ($isSelf): ?><span class="small text-muted">(you)</span><?php endif; ?>
                        </div>
                        <div class="small text-muted"><?= e($row['email']) ?></div>
                    </td>
                    <td><?= role_badge($row['role']) ?></td>
                    <td><?= $row['department'] !== null ? e($row['department']) : '<span class="text-muted">—</span>' ?></td>
                    <td class="text-center"><?= (int) $row['request_count'] ?></td>
                    <td><?= account_badge($row['status']) ?></td>
                    <td class="text-nowrap small"><?= format_date($row['created_at'], false) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url('/admin/users/edit?id=' . $id) ?>" class="btn btn-sm btn-outline-secondary"
                           title="Edit" aria-label="Edit <?= e($row['full_name']) ?>">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <?php if (!$isSelf): ?>
                            <form method="post" action="<?= url('/admin/users/status') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <?php if ($isActive): ?>
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Deactivate"
                                            aria-label="Deactivate <?= e($row['full_name']) ?>"
                                            data-confirm="Deactivate <?= e($row['full_name']) ?>? They will not be able to log in.">
                                        <i class="bi bi-person-slash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Activate"
                                            aria-label="Activate <?= e($row['full_name']) ?>">
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>

                        <?php if ($canDelete): ?>
                            <form method="post" action="<?= url('/admin/users/delete') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                        aria-label="Delete <?= e($row['full_name']) ?>"
                                        data-confirm="Delete the account of <?= e($row['full_name']) ?>? This cannot be undone.">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        <?php elseif (!$isSelf): ?>
                            <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip"
                                  title="Has submitted requests. Deactivate instead.">
                                <button type="button" class="btn btn-sm btn-outline-danger" disabled aria-label="Cannot delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php $basePath = '/admin/users'; require APP_PATH . '/views/partials/pagination.php'; ?>
</div>
