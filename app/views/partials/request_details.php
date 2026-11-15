<?php
// Details of one request, shared by the admin and requester "view" pages.
// Variables: $request (row from MaintenanceRequest::find()).
?>
<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><?= e($request['title']) ?></span>
        <?= status_badge($request['status']) ?>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted fw-normal">Reference no.</dt>
            <dd class="col-sm-8 fw-semibold"><?= e($request['reference_no']) ?></dd>

            <dt class="col-sm-4 text-muted fw-normal">Category</dt>
            <dd class="col-sm-8"><?= e($request['category']) ?></dd>

            <dt class="col-sm-4 text-muted fw-normal">Priority</dt>
            <dd class="col-sm-8"><?= priority_badge($request['priority']) ?></dd>

            <dt class="col-sm-4 text-muted fw-normal">Location</dt>
            <dd class="col-sm-8"><?= e($request['location']) ?></dd>

            <dt class="col-sm-4 text-muted fw-normal">Submitted</dt>
            <dd class="col-sm-8"><?= format_date($request['date_submitted']) ?></dd>

            <dt class="col-sm-4 text-muted fw-normal">Last updated</dt>
            <dd class="col-sm-8"><?= format_date($request['updated_at']) ?></dd>

            <?php if ($request['date_completed'] !== null): ?>
                <dt class="col-sm-4 text-muted fw-normal">Completed</dt>
                <dd class="col-sm-8"><?= format_date($request['date_completed']) ?></dd>
            <?php endif; ?>

            <dt class="col-sm-4 text-muted fw-normal">Handled by</dt>
            <dd class="col-sm-8"><?= $request['handler_name'] !== null ? e($request['handler_name']) : '<span class="text-muted">Not yet reviewed</span>' ?></dd>
        </dl>

        <hr>

        <h2 class="h6">Description</h2>
        <p class="mb-0" style="white-space: pre-line;"><?= e($request['description']) ?></p>

        <?php if (!empty($request['admin_remarks'])): ?>
            <div class="alert alert-light border mt-4 mb-0">
                <div class="small fw-semibold text-muted mb-1">
                    <i class="bi bi-chat-left-text me-1"></i>Remarks from the maintenance office
                </div>
                <div style="white-space: pre-line;"><?= e($request['admin_remarks']) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($request['photo'])): ?>
            <?php $photoUrl = url('/requests/photo?id=' . (int) $request['request_id']); ?>
            <h2 class="h6 mt-4">Photo</h2>
            <a href="<?= $photoUrl ?>" target="_blank" rel="noopener">
                <img src="<?= $photoUrl ?>" alt="Photo of the problem" class="img-fluid rounded border" style="max-height: 320px;">
            </a>
        <?php endif; ?>
    </div>
</div>

<?php require APP_PATH . '/views/partials/request_timeline.php'; ?>
