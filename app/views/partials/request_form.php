<?php
// Request form, shared by "New Request" and both "Edit" pages.
// Variables:
//   $form        field values (title, category, location, description, priority)
//   $errors      messages keyed by field name
//   $action      route the form posts to, e.g. '/requester/requests/create'
//   $submitLabel text of the submit button
//   $cancelUrl   where the Cancel button goes
//   $request     the request being edited, or null for a new one
$request    = $request ?? null;
$hasError   = fn(string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
$errorText  = fn(string $field): string => isset($errors[$field])
    ? '<div class="invalid-feedback">' . e($errors[$field]) . '</div>'
    : '';
?>
<?php if ($errors): ?>
    <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>Please correct the highlighted fields.
    </div>
<?php endif; ?>

<form method="post" action="<?= url($action) ?>" enctype="multipart/form-data" class="row g-3">
    <?= csrf_field() ?>
    <?php if ($request !== null): ?>
        <input type="hidden" name="id" value="<?= (int) $request['request_id'] ?>">
    <?php endif; ?>

    <div class="col-12">
        <label for="title" class="form-label required">Title</label>
        <input type="text" class="form-control<?= $hasError('title') ?>" id="title" name="title"
               value="<?= e($form['title']) ?>" maxlength="150" required
               placeholder="Short summary, e.g. Aircon not cooling">
        <?= $errorText('title') ?>
    </div>

    <div class="col-md-6">
        <label for="category" class="form-label required">Category</label>
        <select class="form-select<?= $hasError('category') ?>" id="category" name="category" required>
            <option value="">Choose a category</option>
            <?php foreach (MaintenanceRequest::CATEGORIES as $category): ?>
                <option value="<?= e($category) ?>" <?= $form['category'] === $category ? 'selected' : '' ?>>
                    <?= e($category) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= $errorText('category') ?>
    </div>

    <div class="col-md-6">
        <label for="priority" class="form-label required">Priority</label>
        <select class="form-select<?= $hasError('priority') ?>" id="priority" name="priority" required>
            <?php foreach (MaintenanceRequest::PRIORITIES as $priority): ?>
                <option value="<?= e($priority) ?>" <?= $form['priority'] === $priority ? 'selected' : '' ?>>
                    <?= e($priority) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= $errorText('priority') ?>
    </div>

    <div class="col-12">
        <label for="location" class="form-label required">Location</label>
        <input type="text" class="form-control<?= $hasError('location') ?>" id="location" name="location"
               value="<?= e($form['location']) ?>" maxlength="150" required
               placeholder="Building and room, e.g. CCS Building, Room 204">
        <?= $errorText('location') ?>
    </div>

    <div class="col-12">
        <label for="description" class="form-label required">Description</label>
        <textarea class="form-control<?= $hasError('description') ?>" id="description" name="description"
                  rows="5" required data-maxlength="<?= MaintenanceRequest::MAX_DESCRIPTION ?>"
                  placeholder="What is wrong, since when, and anything else the technician should know"><?= e($form['description']) ?></textarea>
        <?= $errorText('description') ?>
    </div>

    <div class="col-12">
        <label for="photo" class="form-label">Photo <span class="text-muted fw-normal">(optional)</span></label>

        <?php if ($request !== null && !empty($request['photo'])): ?>
            <div class="d-flex align-items-center gap-3 mb-2">
                <img src="<?= url('/requests/photo?id=' . (int) $request['request_id']) ?>"
                     alt="Current photo" class="rounded border" style="width: 96px; height: 72px; object-fit: cover;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo" value="1">
                    <label class="form-check-label" for="remove_photo">Remove current photo</label>
                </div>
            </div>
        <?php endif; ?>

        <input type="file" class="form-control<?= $hasError('photo') ?>" id="photo" name="photo"
               accept="image/jpeg,image/png">
        <div class="form-text">
            JPG or PNG, up to 2 MB.<?= ($request !== null && !empty($request['photo'])) ? ' Choosing a new photo replaces the current one.' : '' ?>
        </div>
        <?= $errorText('photo') ?>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
        <a href="<?= e($cancelUrl) ?>" class="btn btn-light border">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2 me-1"></i><?= e($submitLabel) ?>
        </button>
    </div>
</form>
