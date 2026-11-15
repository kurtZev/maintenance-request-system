<?php
// Database operations for the maintenance_requests table.

class MaintenanceRequest
{
    // These lists match the ENUM columns in database/database.sql.
    public const CATEGORIES = ['Electrical', 'Plumbing', 'Air Conditioning', 'Carpentry', 'IT/Network', 'Cleaning', 'Other'];
    public const PRIORITIES = ['Low', 'Medium', 'High', 'Urgent'];
    public const STATUSES   = ['Pending', 'In Progress', 'Completed', 'Rejected', 'Cancelled'];

    // Statuses an admin can choose. "Cancelled" is set only by the requester.
    public const ADMIN_STATUSES = ['Pending', 'In Progress', 'Completed', 'Rejected'];

    // Start of every reference number: MRS-2026-0001.
    public const REFERENCE_PREFIX = 'MRS';

    public const MAX_DESCRIPTION = 2000;
    public const MAX_REMARKS     = 1000;

    /**
     * Number of requests per status, e.g. ['Pending' => 2, 'Completed' => 5].
     * Pass a requester's user ID to count only that person's requests.
     */
    public static function countByStatus(?int $requesterId = null): array
    {
        $pdo = Database::connection();

        if ($requesterId === null) {
            $stmt = $pdo->query(
                'SELECT status, COUNT(*) FROM maintenance_requests GROUP BY status'
            );
        } else {
            $stmt = $pdo->prepare(
                'SELECT status, COUNT(*) FROM maintenance_requests
                 WHERE requester_id = ? GROUP BY status'
            );
            $stmt->execute([$requesterId]);
        }

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Headline numbers for the admin dashboard, from one aggregate query:
     * total, pending, in_progress, completed, urgent, urgent_open.
     */
    public static function dashboardTotals(): array
    {
        $row = Database::connection()->query(
            "SELECT COUNT(*)                                                         AS total,
                    COALESCE(SUM(status = 'Pending'), 0)                             AS pending,
                    COALESCE(SUM(status = 'In Progress'), 0)                         AS in_progress,
                    COALESCE(SUM(status = 'Completed'), 0)                           AS completed,
                    COALESCE(SUM(priority = 'Urgent'), 0)                            AS urgent,
                    COALESCE(SUM(priority = 'Urgent'
                                 AND status IN ('Pending', 'In Progress')), 0)      AS urgent_open
             FROM maintenance_requests"
        )->fetch();

        return array_map('intval', $row);
    }

    /** The newest requests with the requester's name, for the dashboard. */
    public static function recent(int $limit = 5): array
    {
        $limit = max(1, $limit);

        return Database::connection()->query(
            "SELECT r.request_id, r.reference_no, r.title, r.category, r.location,
                    r.priority, r.status, r.date_submitted, u.full_name AS requester_name
             FROM maintenance_requests r
             JOIN users u ON u.user_id = r.requester_id
             ORDER BY r.date_submitted DESC, r.request_id DESC
             LIMIT $limit"
        )->fetchAll();
    }

    /**
     * Check the fields of the request form.
     * Returns [cleaned data, errors]; errors is keyed by field name and empty when valid.
     */
    public static function validate(array $input): array
    {
        // Only plain text is accepted; an array (title[]=...) counts as empty.
        $text = fn(string $key): string => is_string($input[$key] ?? null) ? trim($input[$key]) : '';

        $data = [
            'title'       => $text('title'),
            'category'    => $text('category'),
            'location'    => $text('location'),
            'description' => $text('description'),
            'priority'    => $text('priority'),
        ];
        $errors = [];

        if ($data['title'] === '') {
            $errors['title'] = 'Enter a short title for the problem.';
        } elseif (mb_strlen($data['title']) > 150) {
            $errors['title'] = 'The title must be 150 characters or fewer.';
        }

        if (!in_array($data['category'], self::CATEGORIES, true)) {
            $errors['category'] = 'Choose a category.';
        }

        if ($data['location'] === '') {
            $errors['location'] = 'Enter where the problem is (building and room).';
        } elseif (mb_strlen($data['location']) > 150) {
            $errors['location'] = 'The location must be 150 characters or fewer.';
        }

        if ($data['description'] === '') {
            $errors['description'] = 'Describe the problem.';
        } elseif (mb_strlen($data['description']) > self::MAX_DESCRIPTION) {
            $errors['description'] = 'The description must be ' . self::MAX_DESCRIPTION . ' characters or fewer.';
        }

        if (!in_array($data['priority'], self::PRIORITIES, true)) {
            $errors['priority'] = 'Choose a priority.';
        }

        return [$data, $errors];
    }

    /**
     * Search and filter requests, one page at a time.
     *
     * @param array    $filters     keys: q, status, category, priority, date_from, date_to ('' = no filter)
     * @param int|null $requesterId only this requester's requests, or null for all
     * @return array ['rows' => list of requests, 'total' => number of matches]
     */
    public static function search(array $filters, ?int $requesterId, int $page, int $perPage): array
    {
        $where  = [];
        $params = [];

        if ($requesterId !== null) {
            $where[]  = 'r.requester_id = ?';
            $params[] = $requesterId;
        }

        if ($filters['q'] !== '') {
            // Escape % and _ so they are searched as normal characters.
            $like     = '%' . addcslashes($filters['q'], '%_\\') . '%';
            $where[]  = '(r.reference_no LIKE ? OR r.title LIKE ? OR r.location LIKE ? OR u.full_name LIKE ?)';
            array_push($params, $like, $like, $like, $like);
        }

        foreach (['status', 'category', 'priority'] as $field) {
            if ($filters[$field] !== '') {
                $where[]  = "r.$field = ?";
                $params[] = $filters[$field];
            }
        }

        if ($filters['date_from'] !== '') {
            $where[]  = 'r.date_submitted >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if ($filters['date_to'] !== '') {
            $where[]  = 'r.date_submitted <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $from     = 'FROM maintenance_requests r JOIN users u ON u.user_id = r.requester_id';
        $pdo      = Database::connection();

        $countStmt = $pdo->prepare("SELECT COUNT(*) $from $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $limit  = max(1, $perPage);
        $offset = max(0, ($page - 1) * $limit);

        $stmt = $pdo->prepare(
            "SELECT r.request_id, r.reference_no, r.title, r.category, r.location,
                    r.priority, r.status, r.date_submitted, u.full_name AS requester_name
             $from $whereSql
             ORDER BY r.date_submitted DESC, r.request_id DESC
             LIMIT $limit OFFSET $offset"
        );
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(), 'total' => $total];
    }

    /** One request with the requester's and handler's names, or null if there is none. */
    public static function find(int $requestId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT r.*,
                    u.full_name      AS requester_name,
                    u.email          AS requester_email,
                    u.contact_number AS requester_contact,
                    u.department     AS requester_department,
                    h.full_name      AS handler_name
             FROM maintenance_requests r
             JOIN users u      ON u.user_id = r.requester_id
             LEFT JOIN users h ON h.user_id = r.handled_by
             WHERE r.request_id = ?
             LIMIT 1'
        );
        $stmt->execute([$requestId]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Save a new request and give it a reference number such as MRS-2026-0006.
     * Returns the new request ID.
     */
    public static function create(array $data, int $requesterId, ?string $photo): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            // The reference number is built from the new ID, so it is inserted
            // with a temporary unique value first and replaced right after.
            $stmt = $pdo->prepare(
                'INSERT INTO maintenance_requests
                    (reference_no, requester_id, title, category, location, description,
                     priority, status, photo, date_submitted)
                 VALUES (?, ?, ?, ?, ?, ?, ?, \'Pending\', ?, ?)'
            );
            $stmt->execute([
                'TMP-' . bin2hex(random_bytes(6)),
                $requesterId,
                $data['title'],
                $data['category'],
                $data['location'],
                $data['description'],
                $data['priority'],
                $photo,
                date('Y-m-d H:i:s'),
            ]);

            $requestId   = (int) $pdo->lastInsertId();
            $referenceNo = sprintf('%s-%s-%04d', self::REFERENCE_PREFIX, date('Y'), $requestId);

            $pdo->prepare('UPDATE maintenance_requests SET reference_no = ? WHERE request_id = ?')
                ->execute([$referenceNo, $requestId]);

            $pdo->commit();
            return $requestId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Change the details of a request (not its status). */
    public static function update(int $requestId, array $data, ?string $photo): void
    {
        Database::connection()->prepare(
            'UPDATE maintenance_requests
             SET title = ?, category = ?, location = ?, description = ?, priority = ?, photo = ?
             WHERE request_id = ?'
        )->execute([
            $data['title'],
            $data['category'],
            $data['location'],
            $data['description'],
            $data['priority'],
            $photo,
            $requestId,
        ]);
    }

    /**
     * Set the status and remarks, and record which admin did it.
     * The completion date is set the first time a request becomes Completed,
     * and cleared if it is moved back to another status.
     */
    public static function updateStatus(int $requestId, string $status, ?string $remarks, int $adminId): void
    {
        Database::connection()->prepare(
            'UPDATE maintenance_requests
             SET status = ?,
                 admin_remarks = ?,
                 handled_by = ?,
                 date_completed = CASE WHEN ? = \'Completed\' THEN COALESCE(date_completed, ?) ELSE NULL END
             WHERE request_id = ?'
        )->execute([$status, $remarks, $adminId, $status, date('Y-m-d H:i:s'), $requestId]);
    }

    /**
     * Requester cancels their own request. Only works while it is still Pending.
     * Returns true if the request was cancelled.
     */
    public static function cancel(int $requestId, int $requesterId): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE maintenance_requests SET status = \'Cancelled\'
             WHERE request_id = ? AND requester_id = ? AND status = \'Pending\''
        );
        $stmt->execute([$requestId, $requesterId]);

        return $stmt->rowCount() > 0;
    }

    /** Permanently remove a request. Returns true if a row was deleted. */
    public static function delete(int $requestId): bool
    {
        $stmt = Database::connection()->prepare('DELETE FROM maintenance_requests WHERE request_id = ?');
        $stmt->execute([$requestId]);

        return $stmt->rowCount() > 0;
    }
}
