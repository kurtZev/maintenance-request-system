<?php
// One of the requester's own requests. Variables: $request.
$isPending = $request['status'] === 'Pending';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('/requester/requests') ?>" class="btn btn-light border btn-sm">
        <i class="bi bi-arrow-left me-1"></i>My requests
    </a>

    <?php if ($isPending): ?>
        <div class="d-flex gap-2">
            <a href="<?= url('/requester/requests/edit?id=' . (int) $request['request_id']) ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="post" action="<?= url('/requester/requests/cancel') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $request['request_id'] ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm"
                        data-confirm="Cancel request <?= e($request['reference_no']) ?>?">
                    <i class="bi bi-x-circle me-1"></i>Cancel request
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-xl-9">
        <?php require APP_PATH . '/views/partials/request_details.php'; ?>

        <?php if ($isPending): ?>
            <p class="small text-muted">
                <i class="bi bi-info-circle me-1"></i>This request is waiting for review. You can edit or cancel it until then.
            </p>
        <?php endif; ?>
    </div>
</div>
