<?php
// Database operations and validation for the users table.

class User
{
    // These lists match the ENUM columns in database/database.sql.
    public const ROLES    = ['admin', 'requester'];
    public const STATUSES = ['active', 'inactive'];

    public const MIN_PASSWORD = 8;
    public const MAX_PASSWORD = 72;   // password_hash() (bcrypt) uses only the first 72 bytes

    /** One user by email (used at login), or null if there is none. */
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT user_id, full_name, email, password, role, status
             FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);

        return $stmt->fetch() ?: null;
    }

    /** One user by ID (without the password hash), or null if there is none. */
    public static function findById(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT user_id, full_name, email, role, contact_number, department, status, created_at, updated_at
             FROM users WHERE user_id = ? LIMIT 1'
        );
        $stmt->execute([$userId]);

        return $stmt->fetch() ?: null;
    }

    /** Number of accounts that are allowed to log in. */
    public static function countActive(): int
    {
        return (int) Database::connection()
            ->query("SELECT COUNT(*) FROM users WHERE status = 'active'")
            ->fetchColumn();
    }

    /** Number of active administrators, optionally not counting one user. */
    public static function countActiveAdmins(?int $exceptUserId = null): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active' AND user_id <> ?"
        );
        $stmt->execute([$exceptUserId ?? 0]);

        return (int) $stmt->fetchColumn();
    }

    /** True if another account already uses this email. */
    public static function emailTaken(string $email, ?int $exceptUserId = null): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT 1 FROM users WHERE email = ? AND user_id <> ? LIMIT 1'
        );
        $stmt->execute([$email, $exceptUserId ?? 0]);

        return (bool) $stmt->fetchColumn();
    }

    /** Number of maintenance requests this user submitted. */
    public static function countRequests(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM maintenance_requests WHERE requester_id = ?'
        );
        $stmt->execute([$userId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Check the account form.
     *
     * @param array    $input           submitted values
     * @param bool     $requirePassword true for new accounts; false when editing (blank = keep current)
     * @param bool     $withRole        true when an admin sets role and status
     * @param int|null $userId          the account being edited, so its own email is not "taken"
     * @return array [cleaned data, errors keyed by field name]; data['password'] is '' when unchanged
     */
    public static function validate(array $input, bool $requirePassword, bool $withRole, ?int $userId = null): array
    {
        $text = fn(string $key): string => is_string($input[$key] ?? null) ? trim($input[$key]) : '';

        $data = [
            'full_name'      => $text('full_name'),
            'email'          => strtolower($text('email')),
            'contact_number' => $text('contact_number'),
            'department'     => $text('department'),
            'role'           => $withRole ? $text('role') : 'requester',
            'status'         => $withRole ? $text('status') : 'active',
            // Passwords are not trimmed: spaces are allowed characters.
            'password'       => is_string($input['password'] ?? null) ? $input['password'] : '',
        ];
        $confirm = is_string($input['confirm_password'] ?? null) ? $input['confirm_password'] : '';
        $errors  = [];

        if ($data['full_name'] === '') {
            $errors['full_name'] = 'Enter the full name.';
        } elseif (mb_strlen($data['full_name']) > 100) {
            $errors['full_name'] = 'The name must be 100 characters or fewer.';
        }

        if ($data['email'] === '') {
            $errors['email'] = 'Enter an email address.';
        } elseif (mb_strlen($data['email']) > 150 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address, e.g. name@example.com.';
        } elseif (self::emailTaken($data['email'], $userId)) {
            $errors['email'] = 'Another account already uses this email.';
        }

        if ($data['contact_number'] !== '' && !preg_match('/^\+?[0-9][0-9 \-]{6,18}$/', $data['contact_number'])) {
            $errors['contact_number'] = 'Enter a valid phone number, e.g. 09171234567.';
        }

        if (mb_strlen($data['department']) > 100) {
            $errors['department'] = 'The department must be 100 characters or fewer.';
        }

        if ($withRole) {
            if (!in_array($data['role'], self::ROLES, true)) {
                $errors['role'] = 'Choose a role.';
            }
            if (!in_array($data['status'], self::STATUSES, true)) {
                $errors['status'] = 'Choose a status.';
            }
        }

        if ($data['password'] !== '' || $requirePassword) {
            if ($data['password'] === '') {
                $errors['password'] = 'Enter a password.';
            } elseif (strlen($data['password']) < self::MIN_PASSWORD) {
                $errors['password'] = 'The password must be at least ' . self::MIN_PASSWORD . ' characters.';
            } elseif (strlen($data['password']) > self::MAX_PASSWORD) {
                $errors['password'] = 'The password must be ' . self::MAX_PASSWORD . ' characters or fewer.';
            } elseif (!preg_match('/[A-Za-z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
                $errors['password'] = 'The password must contain at least one letter and one number.';
            } elseif ($data['password'] !== $confirm) {
                $errors['confirm_password'] = 'The two passwords do not match.';
            }
        }

        return [$data, $errors];
    }

    /**
     * Search and filter users, one page at a time, with how many requests each submitted.
     *
     * @param array $filters keys: q, role, status ('' = no filter)
     * @return array ['rows' => list of users, 'total' => number of matches]
     */
    public static function search(array $filters, int $page, int $perPage): array
    {
        $where  = [];
        $params = [];

        if ($filters['q'] !== '') {
            $like    = '%' . addcslashes($filters['q'], '%_\\') . '%';
            $where[] = '(u.full_name LIKE ? OR u.email LIKE ? OR u.department LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        foreach (['role', 'status'] as $field) {
            if ($filters[$field] !== '') {
                $where[]  = "u.$field = ?";
                $params[] = $filters[$field];
            }
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $pdo      = Database::connection();

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $limit  = max(1, $perPage);
        $offset = max(0, ($page - 1) * $limit);

        $stmt = $pdo->prepare(
            "SELECT u.user_id, u.full_name, u.email, u.role, u.contact_number, u.department,
                    u.status, u.created_at, COALESCE(r.total, 0) AS request_count
             FROM users u
             LEFT JOIN (
                 SELECT requester_id, COUNT(*) AS total FROM maintenance_requests GROUP BY requester_id
             ) r ON r.requester_id = u.user_id
             $whereSql
             ORDER BY u.full_name ASC, u.user_id ASC
             LIMIT $limit OFFSET $offset"
        );
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(), 'total' => $total];
    }

    /** Save a new account. The password is hashed here. Returns the new user ID. */
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $pdo->prepare(
            'INSERT INTO users (full_name, email, password, role, contact_number, department, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $data['full_name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'],
            $data['contact_number'] === '' ? null : $data['contact_number'],
            $data['department'] === '' ? null : $data['department'],
            $data['status'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** Change an account's details, and its password when a new one was entered. */
    public static function update(int $userId, array $data): void
    {
        Database::connection()->prepare(
            'UPDATE users
             SET full_name = ?, email = ?, role = ?, contact_number = ?, department = ?, status = ?
             WHERE user_id = ?'
        )->execute([
            $data['full_name'],
            $data['email'],
            $data['role'],
            $data['contact_number'] === '' ? null : $data['contact_number'],
            $data['department'] === '' ? null : $data['department'],
            $data['status'],
            $userId,
        ]);

        if ($data['password'] !== '') {
            Database::connection()->prepare('UPDATE users SET password = ? WHERE user_id = ?')
                ->execute([password_hash($data['password'], PASSWORD_DEFAULT), $userId]);
        }
    }

    /** Activate or deactivate an account. */
    public static function setStatus(int $userId, string $status): void
    {
        Database::connection()->prepare('UPDATE users SET status = ? WHERE user_id = ?')
            ->execute([$status, $userId]);
    }

    /**
     * Permanently remove an account. The database refuses this while the user
     * still has requests (foreign key), so check countRequests() first.
     */
    public static function delete(int $userId): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM users WHERE user_id = ?');
        $stmt->execute([$userId]);

        return $stmt->rowCount() > 0;
    }
}
