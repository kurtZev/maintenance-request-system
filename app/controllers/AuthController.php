<?php
// Login and logout.

class AuthController extends Controller
{
    // Hash of a random throwaway value. Matches no real password.
    private const PLACEHOLDER_HASH = '$2y$12$gd5ms6WQhX2FCPq.NrxQve4djYEovKZFw6mfDaHm/lzQae3/3LhHS';

    /** GET /login - show the login form. */
    public function showLogin(): void
    {
        $this->view('auth/login', ['email' => '', 'error' => ''], null);
    }

    /** POST /login - check the email and password. */
    public function login(): void
    {
        // Only plain text is accepted; an array (email[]=...) counts as empty.
        $email    = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $error    = '';

        if (!csrf_is_valid()) {
            $error = 'Your session expired. Please try again.';
        } elseif ($email === '' || $password === '') {
            $error = 'Enter your email and password.';
        } else {
            $user = User::findByEmail($email);

            // One message for "no such email" and "wrong password", so the form
            // does not reveal which emails are registered.
            // An unknown email is still checked against a placeholder hash, so both
            // cases take the same time and cannot be told apart by timing either.
            $passwordOk = password_verify($password, $user['password'] ?? self::PLACEHOLDER_HASH);

            if ($user === null || !$passwordOk) {
                $error = 'Incorrect email or password.';
            } elseif ($user['status'] !== 'active') {
                $error = 'This account is inactive. Contact the administrator.';
            } else {
                AuthMiddleware::startSession($user);
                redirect(dashboard_path($user['role']));
            }
        }

        $this->view('auth/login', ['email' => $email, 'error' => $error], null);
    }

    /** GET /register - show the sign-up form for new requesters. */
    public function showRegister(): void
    {
        $this->view('auth/register', [
            'form'   => ['full_name' => '', 'email' => '', 'contact_number' => '', 'department' => ''],
            'errors' => [],
        ], null);
    }

    /**
     * POST /register - create a requester account and log it in.
     * Public sign-up can only create requesters; admins are added in User Management.
     */
    public function register(): void
    {
        if (!csrf_is_valid()) {
            set_flash('danger', 'Your session expired. Please try again.');
            redirect('/register');
        }

        // validate() is called without role fields, so the role is always 'requester'.
        [$data, $errors] = User::validate($_POST, true, false);

        if ($errors) {
            $this->view('auth/register', ['form' => $data, 'errors' => $errors], null);
            return;
        }

        $userId = User::create($data);

        AuthMiddleware::startSession([
            'user_id'   => $userId,
            'full_name' => $data['full_name'],
            'role'      => 'requester',
        ]);
        set_flash('success', 'Welcome! Your account was created. You can now submit maintenance requests.');
        redirect('/requester/dashboard');
    }

    /**
     * POST /logout - end the login. Only reachable through the "Log out" button,
     * which carries a CSRF token, so another website cannot log a user out.
     */
    public function logout(): void
    {
        if (csrf_is_valid()) {
            AuthMiddleware::endSession();
            set_flash('success', 'You have been logged out.');
            redirect('/login');
        }

        redirect('/');
    }
}
