<?php
// Small functions used by controllers and views.

/** Escape a value for safe output in HTML. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Full link to a route, e.g. url('/admin/dashboard'). */
function url(string $path = '/'): string
{
    return BASE_URL . $path;
}

/** Full link to a file in public/assets, e.g. asset('css/style.css'). */
function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/** Go to another route and stop, e.g. redirect('/login'). */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/** True if a page is registered for this path in routes/web.php. */
function route_exists(string $path): bool
{
    return $GLOBALS['router']->has($path);
}

/** Save a one-time message to show on the next page. Types: success, danger, warning, info. */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Return the saved one-time messages and clear them. */
function take_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Return the session's CSRF token, creating it if needed. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input to place inside every POST form. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** True if the submitted form carries the correct CSRF token. */
function csrf_is_valid(): bool
{
    $submitted = $_POST['csrf_token'] ?? '';
    return is_string($submitted)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submitted);
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Dashboard route for a role. */
function dashboard_path(string $role): string
{
    return $role === 'admin' ? '/admin/dashboard' : '/requester/dashboard';
}

/** Readable date such as "Nov 15, 2026 3:05 PM", or a dash when empty. */
function format_date(?string $datetime, bool $withTime = true): string
{
    if ($datetime === null || $datetime === '') {
        return '—';
    }
    return date($withTime ? 'M j, Y g:i A' : 'M j, Y', strtotime($datetime));
}

/** Readable length of time from a number of hours, e.g. "5.5 hours" or "2.3 days". */
function format_duration(?float $hours): string
{
    if ($hours === null) {
        return '—';
    }
    if ($hours < 24) {
        return number_format($hours, 1) . ' hours';
    }
    return number_format($hours / 24, 1) . ' days';
}

/** Color-coded badge for a request status, e.g. "In Progress". */
function status_badge(string $status): string
{
    $class = 'status-' . strtolower(str_replace(' ', '-', $status));
    return '<span class="badge-soft ' . e($class) . '">' . e($status) . '</span>';
}

/** Badge for a user role: "admin" or "requester". */
function role_badge(string $role): string
{
    return '<span class="badge-soft role-' . e($role) . '">' . e(ucfirst($role)) . '</span>';
}

/** Badge for an account status: "active" or "inactive". */
function account_badge(string $status): string
{
    return '<span class="badge-soft account-' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}

/** Color-coded badge for a request priority, e.g. "Urgent". */
function priority_badge(string $priority): string
{
    $class = 'priority-' . strtolower($priority);
    return '<span class="badge-soft ' . e($class) . '">' . e($priority) . '</span>';
}
