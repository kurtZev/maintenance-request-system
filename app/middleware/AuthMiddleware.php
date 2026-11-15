<?php
// Access control. The router calls these before running a controller,
// based on the access value of the route in routes/web.php.

class AuthMiddleware
{
    /** For pages such as login: a logged-in user is sent to their dashboard. */
    public static function requireGuest(): void
    {
        if (is_logged_in()) {
            redirect(dashboard_path($_SESSION['role']));
        }
    }

    /** Allow only logged-in, active users. Everyone else is sent to the login page. */
    public static function requireLogin(): void
    {
        if (!is_logged_in()) {
            set_flash('warning', 'Please log in to continue.');
            redirect('/login');
        }

        // Sign out after a period of inactivity.
        $idleSeconds = time() - ($_SESSION['last_activity'] ?? 0);
        if ($idleSeconds > SESSION_TIMEOUT_MINUTES * 60) {
            self::endSession();
            set_flash('warning', 'Your session expired. Please log in again.');
            redirect('/login');
        }
        $_SESSION['last_activity'] = time();

        // Re-check the account on every page, so a user who was deactivated,
        // deleted or given a new role cannot keep using an old session.
        $user = User::findById((int) $_SESSION['user_id']);

        if ($user === null || $user['status'] !== 'active') {
            self::endSession();
            set_flash('danger', 'Your account is no longer active. Contact the administrator.');
            redirect('/login');
        }

        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
    }

    /** Allow only users with the given role ('admin' or 'requester'). */
    public static function requireRole(string $role): void
    {
        self::requireLogin();

        if ($_SESSION['role'] !== $role) {
            set_flash('danger', 'You do not have permission to open that page.');
            redirect(dashboard_path($_SESSION['role']));
        }
    }

    /** Remember the user after a correct login. */
    public static function startSession(array $user): void
    {
        // A new session ID on login prevents session fixation.
        session_regenerate_id(true);
        unset($_SESSION['flash']);   // drop any leftover "please log in" notice

        $_SESSION['user_id']       = (int) $user['user_id'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['last_activity'] = time();
    }

    /** Forget the user and start a clean session. */
    public static function endSession(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }
}
