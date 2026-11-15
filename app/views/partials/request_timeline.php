<?php
// Progress timeline of one request, worked out from its status and dates.
// Variables: $request (row from MaintenanceRequest::find()).
//
// Each step is [label, detail, state]; state is 'done', 'current', 'todo' or 'stopped'.
// Only the submission and completion dates are stored exactly. Other steps show
// "Last updated", which is the time of the latest change to the request.
$submitted   = format_date($request['date_submitted']);
$lastUpdated = 'Last updated ' . format_date($request['updated_at']);
$reviewedBy  = $request['handler_name'] !== null ? 'Reviewed by ' . $request['handler_name'] : 'Reviewed';

$steps = [['Submitted', $submitted, 'done']];

switch ($request['status']) {
    case 'Pending':
        $steps[] = ['Waiting for review', 'The maintenance office has not acted on this yet', 'current'];
        $steps[] = ['Work in progress', '', 'todo'];
        $steps[] = ['Completed', '', 'todo'];
        break;

    case 'In Progress':
        $steps[] = [$reviewedBy, '', 'done'];
        $steps[] = ['Work in progress', $lastUpdated, 'current'];
        $steps[] = ['Completed', '', 'todo'];
        break;

    case 'Completed':
        $steps[] = [$reviewedBy, '', 'done'];
        $steps[] = ['Work in progress', '', 'done'];
        $steps[] = ['Completed', format_date($request['date_completed']), 'done'];
        break;

    case 'Rejected':
        $steps[] = [$reviewedBy, '', 'done'];
        $steps[] = ['Rejected', $lastUpdated, 'stopped'];
        break;

    case 'Cancelled':
        $steps[] = ['Cancelled by the requester', $lastUpdated, 'stopped'];
        break;
}

$icons = ['done' => 'bi-check-lg', 'current' => 'bi-arrow-right-short', 'todo' => '', 'stopped' => 'bi-x-lg'];
?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-clock-history me-1"></i>Timeline</div>
    <div class="card-body">
        <ol class="timeline mb-0">
            <?php foreach ($steps as [$label, $detail, $state]): ?>
                <li class="timeline-step timeline-<?= e($state) ?>">
                    <span class="timeline-dot" aria-hidden="true">
                        <?php if ($icons[$state] !== ''): ?><i class="bi <?= e($icons[$state]) ?>"></i><?php endif; ?>
                    </span>
                    <div class="timeline-label">
                        <?= e($label) ?>
                        <?php if ($state === 'current'): ?><span class="visually-hidden">(current step)</span><?php endif; ?>
                    </div>
                    <?php if ($detail !== ''): ?>
                        <div class="small text-muted"><?= e($detail) ?></div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</div>
