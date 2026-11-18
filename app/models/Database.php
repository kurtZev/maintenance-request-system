<?php
// Opens the database connection once and shares it with every model.

class Database
{
    private static ?PDO $pdo = null;

    /** The shared PDO connection. Created on first use, then reused. */
    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $config = require ROOT_PATH . '/config/db_connect.php';

            try {
                self::$pdo = new PDO(
                    "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}",
                    $config['user'],
                    $config['pass'],
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );

                // Strict mode: MySQL refuses invalid values (a status that is not in
                // the ENUM list, text longer than its column) rather than quietly
                // saving a blank or cut-off value. XAMPP leaves this off by default.
                // NULLIF/CONCAT_WS avoid a leading comma when the server's mode is empty.
                self::$pdo->exec("SET SESSION sql_mode = CONCAT_WS(',', NULLIF(@@sql_mode, ''), 'STRICT_ALL_TABLES')");
            } catch (PDOException $e) {
                // Log the real reason; never show connection details to visitors.
                error_log('Database connection failed: ' . $e->getMessage());
                http_response_code(500);
                exit(defined('APP_DEBUG') && APP_DEBUG
                    ? 'Database connection failed. Check that MySQL is running and config/db_connect.php is correct.'
                    : 'The system is temporarily unavailable. Please try again later.');
            }
        }

        return self::$pdo;
    }
}
