<?php
// Maintenance request pages for both roles:
//   Admin:     list/search all, view, edit, update status, delete
//   Requester: list/search own, create, view, edit and cancel while Pending
// Access to each method is enforced by its route in routes/web.php.
// Requesters can only reach their own requests (see findOrFail()).

class MaintenanceRequestController extends Controller
{
    private const PER_PAGE = 10;

    /* ================================================================
       ADMIN
       ================================================================ */

    /** GET /admin/requests - all requests with search, filters and pages. */
    public function adminIndex(): void
    {
        $this->listPage('admin/requests', null, 'requests', 'All Requests');
    }

    /** GET /admin/requests/view?id= - details and the status update form. */
    public function adminShow(): void
    {
        $request = $this->findOrFail($this->idFrom($_GET), false);

        $this->view('admin/view_request', [
            'pageTitle'  => $request['reference_no'],
            'activePage' => 'requests',
            'request'    => $request,
        ]);
    }

    /** GET /admin/requests/edit?id= - edit form. */
    public function adminEdit(): void
    {
        $request = $this->findOrFail($this->idFrom($_GET), false);
        $this->showForm('admin/edit_request', $request, $request, [], 'requests');
    }

    /** POST /admin/requests/edit - save the edit form. */
    public function adminUpdate(): void
    {
        $id = $this->idFrom($_POST);
        $this->requireValidForm('/admin/requests/edit?id=' . $id);
        $request = $this->findOrFail($id, false);

        $this->saveChanges($request, 'admin/edit_request', 'requests', '/admin/requests/view?id=' . $id);
    }

    /** POST /admin/requests/status - change status and remarks. */
    public function updateStatus(): void
    {
        $id   = $this->idFrom($_POST);
        $back = '/admin/requests/view?id=' . $id;
        $this->requireValidForm($back);
        $request = $this->findOrFail($id, false);

        $status  = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
        $remarks = is_string($_POST['admin_remarks'] ?? null) ? trim($_POST['admin_remarks']) : '';

        $error = null;
        if ($request['status'] === 'Cancelled') {
            $error = 'This request was cancelled by the requester and can no longer be updated.';
        } elseif (!in_array($status, MaintenanceRequest::ADMIN_STATUSES, true)) {
            $error = 'Choose a valid status.';
        } elseif (mb_strlen($remarks) > MaintenanceRequest::MAX_REMARKS) {
            $error = 'Remarks must be ' . MaintenanceRequest::MAX_REMARKS . ' characters or fewer.';
        } elseif ($status === 'Rejected' && $remarks === '') {
            $error = 'Give a reason in the remarks when rejecting a request.';
        }

        if ($error !== null) {
            set_flash('danger', $error);
            redirect($back);
        }

        MaintenanceRequest::updateStatus($id, $status, $remarks === '' ? null : $remarks, (int) $_SESSION['user_id']);
        set_flash('success', "Request {$request['reference_no']} is now {$status}.");
        redirect($back);
    }

    /** POST /admin/requests/delete - permanently remove a request and its photo. */
    public function destroy(): void
    {
        $this->requireValidForm('/admin/requests');
        $request = $this->findOrFail($this->idFrom($_POST), false);

        MaintenanceRequest::delete((int) $request['request_id']);
        Upload::delete($request['photo']);

        set_flash('success', "Request {$request['reference_no']} was deleted.");
        redirect('/admin/requests');
    }

    /* ================================================================
       REQUESTER
       ================================================================ */

    /** GET /requester/requests - the logged-in requester's own requests. */
    public function requesterIndex(): void
    {
        $this->listPage('requester/my_requests', (int) $_SESSION['user_id'], 'my_requests', 'My Requests');
    }

    /** GET /requester/requests/create - empty request form. */
    public function create(): void
    {
        $this->view('requester/add_request', [
            'pageTitle'  => 'New Request',
            'activePage' => 'add_request',
            'form'       => ['title' => '', 'category' => '', 'location' => '', 'description' => '', 'priority' => 'Medium'],
            'errors'     => [],
        ]);
    }

    /** POST /requester/requests/create - save a new request. */
    public function store(): void
    {
        $this->requireValidForm('/requester/requests/create');

        [$data, $errors]      = MaintenanceRequest::validate($_POST);
        [$photo, $photoError] = Upload::savePhoto($_FILES['photo'] ?? null);
        if ($photoError !== null) {
            $errors['photo'] = $photoError;
        }

        if ($errors) {
            Upload::delete($photo);   // do not keep a photo for a request that was not saved
            $this->view('requester/add_request', [
                'pageTitle'  => 'New Request',
                'activePage' => 'add_request',
                'form'       => $data,
                'errors'     => $errors,
            ]);
            return;
        }

        try {
            $id = MaintenanceRequest::create($data, (int) $_SESSION['user_id'], $photo);
        } catch (Throwable $e) {
            Upload::delete($photo);
            throw $e;
        }

        $request = MaintenanceRequest::find($id);
        set_flash('success', "Your request was submitted. Your reference number is {$request['reference_no']}.");
        redirect('/requester/requests/view?id=' . $id);
    }

    /** GET /requester/requests/view?id= - one of the requester's own requests. */
    public function requesterShow(): void
    {
        $request = $this->findOrFail($this->idFrom($_GET), true);

        $this->view('requester/view_request', [
            'pageTitle'  => $request['reference_no'],
            'activePage' => 'my_requests',
            'request'    => $request,
        ]);
    }

    /** GET /requester/requests/edit?id= - edit form, only while Pending. */
    public function requesterEdit(): void
    {
        $request = $this->findOrFail($this->idFrom($_GET), true);
        $this->requirePending($request);

        $this->showForm('requester/edit_request', $request, $request, [], 'my_requests');
    }

    /** POST /requester/requests/edit - save the edit form, only while Pending. */
    public function requesterUpdate(): void
    {
        $id = $this->idFrom($_POST);
        $this->requireValidForm('/requester/requests/edit?id=' . $id);
        $request = $this->findOrFail($id, true);
        $this->requirePending($request);

        $this->saveChanges($request, 'requester/edit_request', 'my_requests', '/requester/requests/view?id=' . $id);
    }

    /** POST /requester/requests/cancel - cancel an own request while Pending. */
    public function cancel(): void
    {
        $id = $this->idFrom($_POST);
        $this->requireValidForm('/requester/requests/view?id=' . $id);
        $request = $this->findOrFail($id, true);

        if (MaintenanceRequest::cancel($id, (int) $_SESSION['user_id'])) {
            set_flash('success', "Request {$request['reference_no']} was cancelled.");
        } else {
            set_flash('warning', 'Only pending requests can be cancelled.');
        }
        redirect('/requester/requests/view?id=' . $id);
    }

    /* ================================================================
       SHARED
       ================================================================ */

    /** GET /requests/photo?id= - the request's photo, for an admin or the request's owner. */
    public function photo(): void
    {
        $id      = $this->idFrom($_GET);
        $request = $id > 0 ? MaintenanceRequest::find($id) : null;

        $allowed = $request !== null
            && ($_SESSION['role'] === 'admin' || (int) $request['requester_id'] === (int) $_SESSION['user_id']);
        $path = $allowed ? Upload::path($request['photo']) : null;

        if ($path === null) {
            http_response_code(404);
            exit('Photo not found.');
        }

        header('Content-Type: ' . Upload::mimeType($request['photo']));
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    /** Build a list page: read filters and page number, search, show the view. */
    private function listPage(string $view, ?int $requesterId, string $activePage, string $title): void
    {
        $filters = $this->filtersFromQuery();
        $page    = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;

        $result     = MaintenanceRequest::search($filters, $requesterId, $page, self::PER_PAGE);
        $totalPages = max(1, (int) ceil($result['total'] / self::PER_PAGE));

        // A page number past the end (e.g. after deleting) shows the last page.
        if ($page > $totalPages) {
            $page   = $totalPages;
            $result = MaintenanceRequest::search($filters, $requesterId, $page, self::PER_PAGE);
        }

        $this->view($view, [
            'pageTitle'  => $title,
            'activePage' => $activePage,
            'requests'   => $result['rows'],
            'total'      => $result['total'],
            'filters'    => $filters,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /** Read the search and filter values from the address bar, ignoring anything invalid. */
    private function filtersFromQuery(): array
    {
        $get = fn(string $key): string => is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
        $oneOf = fn(string $value, array $allowed): string => in_array($value, $allowed, true) ? $value : '';
        $date = function (string $value): string {
            $d = DateTime::createFromFormat('Y-m-d', $value);
            return ($d && $d->format('Y-m-d') === $value) ? $value : '';
        };

        return [
            'q'         => mb_substr($get('q'), 0, 100),
            'status'    => $oneOf($get('status'), MaintenanceRequest::STATUSES),
            'category'  => $oneOf($get('category'), MaintenanceRequest::CATEGORIES),
            'priority'  => $oneOf($get('priority'), MaintenanceRequest::PRIORITIES),
            'date_from' => $date($get('date_from')),
            'date_to'   => $date($get('date_to')),
        ];
    }

    /** Validate and save the edit form; on errors show the form again. */
    private function saveChanges(array $request, string $view, string $activePage, string $successPath): void
    {
        [$data, $errors]         = MaintenanceRequest::validate($_POST);
        [$newPhoto, $photoError] = Upload::savePhoto($_FILES['photo'] ?? null);
        if ($photoError !== null) {
            $errors['photo'] = $photoError;
        }

        if ($errors) {
            Upload::delete($newPhoto);
            $this->showForm($view, $request, $data, $errors, $activePage);
            return;
        }

        // A new photo replaces the old one; "remove photo" clears it.
        $removeOld = !empty($_POST['remove_photo']);
        $photo     = $newPhoto ?? ($removeOld ? null : $request['photo']);

        MaintenanceRequest::update((int) $request['request_id'], $data, $photo);

        if ($request['photo'] !== $photo) {
            Upload::delete($request['photo']);
        }

        set_flash('success', "Request {$request['reference_no']} was updated.");
        redirect($successPath);
    }

    private function showForm(string $view, array $request, array $form, array $errors, string $activePage): void
    {
        $this->view($view, [
            'pageTitle'  => 'Edit ' . $request['reference_no'],
            'activePage' => $activePage,
            'request'    => $request,
            'form'       => $form,
            'errors'     => $errors,
        ]);
    }

    /** The request ID from a form or the address bar, or 0 if missing or not a positive whole number. */
    private function idFrom(array $source): int
    {
        $id = filter_var($source['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? 0 : $id;
    }

    /**
     * The request with this ID, or back to the list with a message.
     * With $ownOnly, another user's request is treated as not found,
     * so requesters cannot even learn that it exists.
     */
    private function findOrFail(int $id, bool $ownOnly): array
    {
        $request = $id > 0 ? MaintenanceRequest::find($id) : null;

        if ($request === null || ($ownOnly && (int) $request['requester_id'] !== (int) $_SESSION['user_id'])) {
            set_flash('danger', 'That request was not found.');
            redirect($ownOnly ? '/requester/requests' : '/admin/requests');
        }

        return $request;
    }

    /** Requesters may only change a request the office has not acted on yet. */
    private function requirePending(array $request): void
    {
        if ($request['status'] !== 'Pending') {
            set_flash('warning', 'Only pending requests can be edited.');
            redirect('/requester/requests/view?id=' . $request['request_id']);
        }
    }

    /** Stop if the form's CSRF token is missing or wrong. */
    private function requireValidForm(string $backPath): void
    {
        if (!csrf_is_valid()) {
            // An upload bigger than the server allows also arrives with an empty form.
            set_flash('danger', 'The form could not be submitted (session expired or file too large). Please try again.');
            redirect($backPath);
        }
    }
}
