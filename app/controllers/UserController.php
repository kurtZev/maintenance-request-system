<?php
// User management (admin only): list, search, add, edit, activate/deactivate, delete.
// Safeguards: an admin cannot lock themselves out, and the system always
// keeps at least one active administrator.

class UserController extends Controller
{
    private const PER_PAGE = 10;

    /** GET /admin/users - all accounts with search, filters and pages. */
    public function index(): void
    {
        $get     = fn(string $key): string => is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
        $filters = [
            'q'      => mb_substr($get('q'), 0, 100),
            'role'   => in_array($get('role'), User::ROLES, true) ? $get('role') : '',
            'status' => in_array($get('status'), User::STATUSES, true) ? $get('status') : '',
        ];
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;

        $result     = User::search($filters, $page, self::PER_PAGE);
        $totalPages = max(1, (int) ceil($result['total'] / self::PER_PAGE));
        if ($page > $totalPages) {
            $page   = $totalPages;
            $result = User::search($filters, $page, self::PER_PAGE);
        }

        $this->view('admin/users', [
            'pageTitle'  => 'Users',
            'activePage' => 'users',
            'users'      => $result['rows'],
            'total'      => $result['total'],
            'filters'    => $filters,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /** GET /admin/users/create - empty account form. */
    public function create(): void
    {
        $this->showForm(null, [
            'full_name' => '', 'email' => '', 'contact_number' => '', 'department' => '',
            'role' => 'requester', 'status' => 'active',
        ], []);
    }

    /** POST /admin/users/create - save a new account. */
    public function store(): void
    {
        $this->requireValidForm('/admin/users/create');

        [$data, $errors] = User::validate($_POST, true, true);
        if ($errors) {
            $this->showForm(null, $data, $errors);
            return;
        }

        User::create($data);
        set_flash('success', "Account for {$data['full_name']} was created.");
        redirect('/admin/users');
    }

    /** GET /admin/users/edit?id= - edit form. */
    public function edit(): void
    {
        $user = $this->findOrFail($this->idFrom($_GET));
        $this->showForm($user, $user, []);
    }

    /** POST /admin/users/edit - save the edit form. */
    public function update(): void
    {
        $id = $this->idFrom($_POST);
        $this->requireValidForm('/admin/users/edit?id=' . $id);
        $user = $this->findOrFail($id);

        [$data, $errors] = User::validate($_POST, false, true, $id);

        if (!$errors) {
            $lockout = $this->lockoutProblem($user, $data['role'], $data['status']);
            if ($lockout !== null) {
                $errors[$data['status'] !== 'active' ? 'status' : 'role'] = $lockout;
            }
        }

        if ($errors) {
            $this->showForm($user, $data, $errors);
            return;
        }

        User::update($id, $data);

        $message = "Account for {$data['full_name']} was updated.";
        if ($data['password'] !== '') {
            $message .= ' The new password is active now.';
        }
        set_flash('success', $message);
        redirect('/admin/users');
    }

    /** POST /admin/users/status - switch an account between active and inactive. */
    public function toggleStatus(): void
    {
        $this->requireValidForm('/admin/users');
        $user      = $this->findOrFail($this->idFrom($_POST));
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';

        $lockout = $this->lockoutProblem($user, $user['role'], $newStatus);
        if ($lockout !== null) {
            set_flash('danger', $lockout);
            redirect('/admin/users');
        }

        User::setStatus((int) $user['user_id'], $newStatus);
        set_flash('success', $newStatus === 'active'
            ? "{$user['full_name']} can log in again."
            : "{$user['full_name']} was deactivated and can no longer log in.");
        redirect('/admin/users');
    }

    /** POST /admin/users/delete - permanently remove an account that has no requests. */
    public function destroy(): void
    {
        $this->requireValidForm('/admin/users');
        $user = $this->findOrFail($this->idFrom($_POST));
        $id   = (int) $user['user_id'];

        $problem = null;
        if ($id === (int) $_SESSION['user_id']) {
            $problem = 'You cannot delete your own account.';
        } elseif (User::countRequests($id) > 0) {
            // The database also blocks this (foreign key), so request history is never lost.
            $problem = "{$user['full_name']} has submitted requests, so the account cannot be deleted. Deactivate it instead.";
        } elseif ($user['role'] === 'admin' && $user['status'] === 'active' && User::countActiveAdmins($id) === 0) {
            $problem = 'The system needs at least one active administrator.';
        }

        if ($problem !== null) {
            set_flash('danger', $problem);
            redirect('/admin/users');
        }

        User::delete($id);
        set_flash('success', "Account for {$user['full_name']} was deleted.");
        redirect('/admin/users');
    }

    /**
     * Reason a change would lock admins out, or null if it is safe:
     * admins cannot demote or deactivate themselves, and at least one
     * active admin must remain.
     */
    private function lockoutProblem(array $user, string $newRole, string $newStatus): ?string
    {
        $isSelf          = (int) $user['user_id'] === (int) $_SESSION['user_id'];
        $staysActiveAdmin = $newRole === 'admin' && $newStatus === 'active';

        if ($isSelf && !$staysActiveAdmin) {
            return 'You cannot remove your own admin access or deactivate your own account.';
        }

        $isActiveAdmin = $user['role'] === 'admin' && $user['status'] === 'active';
        if ($isActiveAdmin && !$staysActiveAdmin && User::countActiveAdmins((int) $user['user_id']) === 0) {
            return 'The system needs at least one active administrator.';
        }

        return null;
    }

    private function showForm(?array $user, array $form, array $errors): void
    {
        $this->view('admin/user_form', [
            'pageTitle'  => $user === null ? 'Add User' : 'Edit User',
            'activePage' => 'users',
            'user'       => $user,
            'form'       => $form,
            'errors'     => $errors,
            'isSelf'     => $user !== null && (int) $user['user_id'] === (int) $_SESSION['user_id'],
        ]);
    }

    private function idFrom(array $source): int
    {
        $id = filter_var($source['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? 0 : $id;
    }

    private function findOrFail(int $id): array
    {
        $user = $id > 0 ? User::findById($id) : null;
        if ($user === null) {
            set_flash('danger', 'That user was not found.');
            redirect('/admin/users');
        }
        return $user;
    }

    private function requireValidForm(string $backPath): void
    {
        if (!csrf_is_valid()) {
            set_flash('danger', 'Your session expired. Please try again.');
            redirect($backPath);
        }
    }
}
