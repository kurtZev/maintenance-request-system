<?php
// Edit a pending request (requester). Variables: $request, $form, $errors.
?>
<div class="card form-card">
    <div class="card-header">
        Edit request <span class="text-muted fw-normal"><?= e($request['reference_no']) ?></span>
    </div>
    <div class="card-body">
        <?php
        $action      = '/requester/requests/edit';
        $submitLabel = 'Save changes';
        $cancelUrl   = url('/requester/requests/view?id=' . (int) $request['request_id']);
        require APP_PATH . '/views/partials/request_form.php';
        ?>
    </div>
</div>
