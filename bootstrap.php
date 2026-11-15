<?php
// Application setup. Loaded once per request by public/index.php.

define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');

define('APP_NAME', 'Maintenance Request System');

// Minutes of inactivity before a logged-in user is signed out.
define('SESSION_TIMEOUT_MINUTES', 30);

// true  = show full PHP errors in the browser (only while developing).
// false = log errors and show visitors a plain "something went wrong" page.
// Keep this false on InfinityFree: error text reveals file paths and queries.
define('APP_DEBUG', false);

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// Any error nobody caught (e.g. a failed query) ends up here.
set_exception_handler(function (Throwable $e): void {
    error_log('Uncaught ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if (!headers_sent()) {
        http_response_code(500);
    }
    if (APP_DEBUG) {
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        require APP_PATH . '/views/errors/500.php';
    }
});

// Headers sent with every page.
if (PHP_SAPI !== 'cli') {
    header_remove('X-Powered-By');                                  // do not advertise the PHP version
    header('X-Frame-Options: DENY');                                // the site cannot be put inside a frame (clickjacking)
    header("Content-Security-Policy: frame-ancestors 'none'");      // same rule, for newer browsers
    header('X-Content-Type-Options: nosniff');                      // files are only used as their declared type
    header('Referrer-Policy: same-origin');                         // addresses of pages are not sent to other sites
}

// Change this if the system is used outside the Philippines.
date_default_timezone_set('Asia/Manila');

// BASE_URL is the project's path in the address bar, worked out automatically:
// "/maintenance-request-system" on XAMPP, "" when the project sits directly in
// htdocs on InfinityFree. Build links with url('/login'), never by hand.
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptDir = preg_replace('#/public$#', '', $scriptDir);
define('BASE_URL', rtrim($scriptDir, '/'));
unset($scriptDir);

// Load a class file the first time the class is used, e.g. "User" is found
// in app/models/User.php. This replaces a long list of require lines.
spl_autoload_register(function (string $class): void {
    foreach (['core', 'controllers', 'models', 'middleware'] as $folder) {
        $file = APP_PATH . '/' . $folder . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require APP_PATH . '/core/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    // Only accept session IDs this server created. An ID planted by someone
    // else (session fixation) is replaced with a new one.
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_set_cookie_params([
        'lifetime' => 0,                 // cookie ends when the browser closes
        'path'     => '/',
        'httponly' => true,              // JavaScript cannot read the cookie
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

$router = new Router();
