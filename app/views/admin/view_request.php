<?php
// One request (admin): details, requester, status update, delete. Variables: $request.
$isCancelled   = $request['status'] === 'Cancelled';
$currentStatus = in_array($request['status'], MaintenanceRequest::ADMIN_STATUSES, true) ? $request['status'] : 'Pending';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('/admin/requests') ?>" class="btn btn-light border btn-sm">
        <i class="bi bi-arrow-left me-1"></i>All requests
    </a>
    <a href="<?= url('/admin/requests/edit?id=' . (int) $request['request_id']) ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil me-1"></i>Edit details
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <?php require APP_PATH . '/views/partials/request_details.php'; ?>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-arrow-repeat me-1"></i>Update status</div>
            <div class="card-body">
                <?php if ($isCancelled): ?>
                    <p class="text-muted mb-0">The requester cancelled this request, so its status can no longer be changed.</p>
                <?php else: ?>
                    <form method="post" action="<?= url('/admin/requests/status') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $request['request_id'] ?>">

                        <div class="mb-3">
                            <label for="status" class="form-label required">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php foreach (MaintenanceRequest::ADMIN_STATUSES as $option): ?>
                                    <option <?= $currentStatus === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admin_remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="admin_remarks" name="admin_remarks" rows="4"
                                      data-maxlength="<?= MaintenanceRequest::MAX_REMARKS ?>"
                                      placeholder="Action taken, or the reason for rejecting"><?= e($request['admin_remarks'] ?? '') ?></textarea>
                            <div class="form-text">Visible to the requester. Required when rejecting.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check2 me-1"></i>Save status
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-person me-1"></i>Requester</div>
            <div class="card-body">
                <div class="fw-semibold"><?= e($request['requester_name']) ?></div>
                <div class="small text-muted mb-2"><?= e($request['requester_department'] ?? '') ?></div>
                <div class="small"><i class="bi bi-envelope me-2 text-muted"></i><?= e($request['requester_email']) ?></div>
                <?php if (!empty($request['requester_contact'])): ?>
                    <div class="small"><i class="bi bi-telephone me-2 text-muted"></i><?= e($request['requester_contact']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-danger-subtle">
            <div class="card-body">
                <p class="small text-muted mb-2">Deleting removes this request and its photo permanently.</p>
                <form method="post" action="<?= url('/admin/requests/delete') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $request['request_id'] ?>">
                    <button type="submit" class="btn btn-outline-danger w-100"
                            data-confirm="Delete request <?= e($request['reference_no']) ?>? This cannot be undone.">
                        <i class="bi bi-trash me-1"></i>Delete request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
