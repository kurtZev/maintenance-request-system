<?php
// Add or edit an account (admin). Variables: $user (null when adding), $form, $errors, $isSelf.
$isNew     = $user === null;
$hasError  = fn(string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
$errorText = fn(string $field): string => isset($errors[$field])
    ? '<div class="invalid-feedback">' . e($errors[$field]) . '</div>'
    : '';
?>
<div class="mb-3">
    <a href="<?= url('/admin/users') ?>" class="btn btn-light border btn-sm">
        <i class="bi bi-arrow-left me-1"></i>All users
    </a>
</div>

<div class="card form-card">
    <div class="card-header">
        <?php if ($isNew): ?>
            Add a user account
        <?php else: ?>
            Edit account <span class="text-muted fw-normal"><?= e($user['email']) ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($errors): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i>Please correct the highlighted fields.
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url($isNew ? '/admin/users/create' : '/admin/users/edit') ?>" class="row g-3">
            <?= csrf_field() ?>
            <?php if (!$isNew): ?>
                <input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>">
            <?php endif; ?>

            <div class="col-12">
                <label for="full_name" class="form-label required">Full name</label>
                <input type="text" class="form-control<?= $hasError('full_name') ?>" id="full_name" name="full_name"
                       value="<?= e($form['full_name']) ?>" maxlength="100" required>
                <?= $errorText('full_name') ?>
            </div>

            <div class="col-12">
                <label for="email" class="form-label required">Email</label>
                <input type="email" class="form-control<?= $hasError('email') ?>" id="email" name="email"
                       value="<?= e($form['email']) ?>" maxlength="150" required autocomplete="off">
                <?= $errorText('email') ?>
            </div>

            <div class="col-md-6">
                <label for="contact_number" class="form-label">Contact number</label>
                <input type="tel" class="form-control<?= $hasError('contact_number') ?>" id="contact_number" name="contact_number"
                       value="<?= e($form['contact_number'] ?? '') ?>" maxlength="20" placeholder="09171234567">
                <?= $errorText('contact_number') ?>
            </div>

            <div class="col-md-6">
                <label for="department" class="form-label">Department / office</label>
                <input type="text" class="form-control<?= $hasError('department') ?>" id="department" name="department"
                       value="<?= e($form['department'] ?? '') ?>" maxlength="100">
                <?= $errorText('department') ?>
            </div>

            <div class="col-md-6">
                <label for="role" class="form-label required">Role</label>
                <select class="form-select<?= $hasError('role') ?>" id="role" name="role" required>
                    <?php foreach (User::ROLES as $option): ?>
                        <option value="<?= e($option) ?>" <?= $form['role'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $errorText('role') ?>
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label required">Status</label>
                <select class="form-select<?= $hasError('status') ?>" id="status" name="status" required>
                    <?php foreach (User::STATUSES as $option): ?>
                        <option value="<?= e($option) ?>" <?= $form['status'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $errorText('status') ?>
            </div>

            <?php if ($isSelf): ?>
                <div class="col-12">
                    <div class="form-text"><i class="bi bi-info-circle me-1"></i>This is your own account. You cannot remove your admin role or deactivate it.</div>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <hr class="my-1">
                <h2 class="h6 mt-3 mb-0"><?= $isNew ? 'Password' : 'Reset password' ?></h2>
                <?php if (!$isNew): ?>
                    <div class="form-text">Leave both fields blank to keep the current password.</div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label<?= $isNew ? ' required' : '' ?>"><?= $isNew ? 'Password' : 'New password' ?></label>
                <div class="input-group has-validation">
                    <input type="password" class="form-control<?= $hasError('password') ?>" id="password" name="password"
                           maxlength="<?= User::MAX_PASSWORD ?>" <?= $isNew ? 'required' : '' ?> autocomplete="new-password">
                    <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Show or hide password">
                        <i class="bi bi-eye"></i>
                    </button>
                    <?= $errorText('password') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="confirm_password" class="form-label<?= $isNew ? ' required' : '' ?>">Confirm password</label>
                <div class="input-group has-validation">
                    <input type="password" class="form-control<?= $hasError('confirm_password') ?>" id="confirm_password" name="confirm_password"
                           maxlength="<?= User::MAX_PASSWORD ?>" <?= $isNew ? 'required' : '' ?> autocomplete="new-password">
                    <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Show or hide password">
                        <i class="bi bi-eye"></i>
                    </button>
                    <?= $errorText('confirm_password') ?>
                </div>
            </div>

            <div class="col-12 form-text mt-1">
                At least <?= User::MIN_PASSWORD ?> characters, with at least one letter and one number.
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="<?= url('/admin/users') ?>" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2 me-1"></i><?= $isNew ? 'Create account' : 'Save changes' ?>
                </button>
            </div>
        </form>
    </div>
</div>
