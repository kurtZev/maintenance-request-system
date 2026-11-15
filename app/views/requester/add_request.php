<?php
// New request form (requester). Variables: $form, $errors.
?>
<div class="card form-card">
    <div class="card-header">Submit a maintenance request</div>
    <div class="card-body">
        <p class="text-muted">
            Describe the problem as clearly as you can. You can edit or cancel the request
            until the maintenance office starts working on it.
        </p>
        <?php
        $request     = null;
        $action      = '/requester/requests/create';
        $submitLabel = 'Submit request';
        $cancelUrl   = url('/requester/requests');
        require APP_PATH . '/views/partials/request_form.php';
        ?>
    </div>
</div>
