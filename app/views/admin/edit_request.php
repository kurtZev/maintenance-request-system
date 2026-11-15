<?php
// Edit a request's details (admin). Variables: $request, $form, $errors.
?>
<div class="card form-card">
    <div class="card-header">
        Edit request <span class="text-muted fw-normal"><?= e($request['reference_no']) ?></span>
    </div>
    <div class="card-body">
        <?php
        $action      = '/admin/requests/edit';
        $submitLabel = 'Save changes';
        $cancelUrl   = url('/admin/requests/view?id=' . (int) $request['request_id']);
        require APP_PATH . '/views/partials/request_form.php';
        ?>
    </div>
</div>
