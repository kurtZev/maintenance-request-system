<?php
/**
 * Creates an administrator account from the command line.
 * Use it once on a fresh database, when no admin exists yet to add one
 * through User Management.
 *
 * Run from the project folder (MySQL must be running):
 *     C:\xampp\php\php.exe database\create_admin.php
 *
 * It cannot be opened in a browser: the database/ folder is blocked by
 * .htaccess, and the script also refuses to run outside the command line.
 * The password is never saved anywhere except as a password_hash() value.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

require APP_PATH . '/models/Database.php';
require APP_PATH . '/models/User.php';

/** Print a question and return the typed answer. */
function ask(string $question): string
{
    echo $question;
    $answer = fgets(STDIN);
    return $answer === false ? '' : rtrim($answer, "\r\n");
}

echo "Create an administrator account\n";
echo "-------------------------------\n";

$input = [
    'full_name'        => ask('Full name: '),
    'email'            => ask('Email: '),
    'password'         => ask('Password (min. ' . User::MIN_PASSWORD . ' characters, a letter and a number): '),
    'confirm_password' => ask('Confirm password: '),
    'role'             => 'admin',
    'status'           => 'active',
];

// The same rules as the website: valid unique email, strong password, etc.
[$data, $errors] = User::validate($input, true, true);

if ($errors) {
    echo "\nThe account was NOT created:\n";
    foreach ($errors as $message) {
        echo " - $message\n";
    }
    exit(1);
}

$userId = User::create($data);   // hashes the password with password_hash()

echo "\nAdministrator account created (user ID $userId).\n";
echo "Log in with {$data['email']} at /login.\n";
echo "Tip: clear this terminal window, since the password was typed visibly.\n";
