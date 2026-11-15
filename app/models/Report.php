<?php
// Summary queries for the Reports page. Every method covers requests
// SUBMITTED between $from and $to (both 'Y-m-d', inclusive).

class Report
{
    // Pass these as $from and $to to cover every request ever submitted.
    public const ALL_TIME_FROM = '1000-01-01';
    public const ALL_TIME_TO   = '9999-12-31';

    /**
     * Totals for the range: total, completed, pending, in_progress, rejected,
     * cancelled, and avg_hours (average hours from submission to completion).
     */
    public static function summary(string $from, string $to): array
    {
        $stmt = self::run(
            "SELECT COUNT(*)                         AS total,
                    COALESCE(SUM(status = 'Completed'), 0)   AS completed,
                    COALESCE(SUM(status = 'Pending'), 0)     AS pending,
                    COALESCE(SUM(status = 'In Progress'), 0) AS in_progress,
                    COALESCE(SUM(status = 'Rejected'), 0)    AS rejected,
                    COALESCE(SUM(status = 'Cancelled'), 0)   AS cancelled,
                    AVG(CASE WHEN status = 'Completed' AND date_completed IS NOT NULL
                             THEN TIMESTAMPDIFF(MINUTE, date_submitted, date_completed) / 60 END) AS avg_hours
             FROM maintenance_requests
             WHERE date_submitted BETWEEN ? AND ?",
            $from, $to
        );

        $row = $stmt->fetch();
        foreach (['total', 'completed', 'pending', 'in_progress', 'rejected', 'cancelled'] as $key) {
            $row[$key] = (int) $row[$key];
        }
        $row['avg_hours'] = $row['avg_hours'] === null ? null : (float) $row['avg_hours'];

        return $row;
    }

    /** Count per status, in the fixed status order, e.g. ['Pending' => 2, ...]. */
    public static function byStatus(string $from, string $to): array
    {
        $counts = self::run(
            'SELECT status, COUNT(*) FROM maintenance_requests
             WHERE date_submitted BETWEEN ? AND ? GROUP BY status',
            $from, $to
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        $result = [];
        foreach (MaintenanceRequest::STATUSES as $status) {
            $result[$status] = (int) ($counts[$status] ?? 0);
        }
        return $result;
    }

    /**
     * Breakdown by a column ('category' or 'priority'): one row per value with
     * total, open (Pending + In Progress), completed, closed_other (Rejected +
     * Cancelled) and avg_hours. Values with no requests are included as zeros.
     */
    public static function breakdown(string $column, string $from, string $to): array
    {
        $allowed = ['category' => MaintenanceRequest::CATEGORIES, 'priority' => MaintenanceRequest::PRIORITIES];
        if (!isset($allowed[$column])) {
            throw new InvalidArgumentException("Cannot group by $column");
        }

        $rows = self::run(
            "SELECT $column AS label,
                    COUNT(*) AS total,
                    SUM(status IN ('Pending', 'In Progress')) AS open,
                    SUM(status = 'Completed')                 AS completed,
                    SUM(status IN ('Rejected', 'Cancelled'))  AS closed_other,
                    AVG(CASE WHEN status = 'Completed' AND date_completed IS NOT NULL
                             THEN TIMESTAMPDIFF(MINUTE, date_submitted, date_completed) / 60 END) AS avg_hours
             FROM maintenance_requests
             WHERE date_submitted BETWEEN ? AND ?
             GROUP BY $column",
            $from, $to
        )->fetchAll(PDO::FETCH_UNIQUE);

        $result = [];
        foreach ($allowed[$column] as $value) {
            $row = $rows[$value] ?? [];
            $result[] = [
                'label'        => $value,
                'total'        => (int) ($row['total'] ?? 0),
                'open'         => (int) ($row['open'] ?? 0),
                'completed'    => (int) ($row['completed'] ?? 0),
                'closed_other' => (int) ($row['closed_other'] ?? 0),
                'avg_hours'    => isset($row['avg_hours']) ? (float) $row['avg_hours'] : null,
            ];
        }
        return $result;
    }

    /**
     * Requests submitted and completed per month, e.g.
     * ['2026-11' => ['submitted' => 5, 'completed' => 1], ...].
     * Every month in the range is listed, even with zero requests.
     */
    public static function monthly(string $from, string $to): array
    {
        $rows = self::run(
            "SELECT DATE_FORMAT(date_submitted, '%Y-%m') AS month,
                    COUNT(*)                   AS submitted,
                    SUM(status = 'Completed')  AS completed
             FROM maintenance_requests
             WHERE date_submitted BETWEEN ? AND ?
             GROUP BY month",
            $from, $to
        )->fetchAll(PDO::FETCH_UNIQUE);

        $result = [];
        $month  = new DateTime(substr($from, 0, 7) . '-01');
        $last   = new DateTime(substr($to, 0, 7) . '-01');
        while ($month <= $last) {
            $key          = $month->format('Y-m');
            $result[$key] = [
                'submitted' => (int) ($rows[$key]['submitted'] ?? 0),
                'completed' => (int) ($rows[$key]['completed'] ?? 0),
            ];
            $month->modify('+1 month');
        }
        return $result;
    }

    /** The requesters who submitted the most requests in the range. */
    public static function topRequesters(string $from, string $to, int $limit = 5): array
    {
        $limit = max(1, $limit);

        return self::run(
            "SELECT u.full_name, u.department, COUNT(*) AS total,
                    SUM(r.status = 'Completed') AS completed
             FROM maintenance_requests r
             JOIN users u ON u.user_id = r.requester_id
             WHERE r.date_submitted BETWEEN ? AND ?
             GROUP BY u.user_id, u.full_name, u.department
             ORDER BY total DESC, u.full_name ASC
             LIMIT $limit",
            $from, $to
        )->fetchAll();
    }

    /** Every request in the range with requester and handler names (for CSV export). */
    public static function requestsForExport(string $from, string $to): array
    {
        return self::run(
            'SELECT r.reference_no, r.title, r.category, r.location, r.priority, r.status,
                    u.full_name AS requester_name, u.department AS requester_department,
                    h.full_name AS handler_name, r.admin_remarks,
                    r.date_submitted, r.date_completed
             FROM maintenance_requests r
             JOIN users u      ON u.user_id = r.requester_id
             LEFT JOIN users h ON h.user_id = r.handled_by
             WHERE r.date_submitted BETWEEN ? AND ?
             ORDER BY r.date_submitted ASC, r.request_id ASC',
            $from, $to
        )->fetchAll();
    }

    /** Date of the oldest request, or null when there are none (for the "All time" range). */
    public static function firstRequestDate(): ?string
    {
        $date = Database::connection()
            ->query('SELECT DATE(MIN(date_submitted)) FROM maintenance_requests')
            ->fetchColumn();

        return $date ?: null;
    }

    /** Run a query filtered by the date range (from 00:00:00 to 23:59:59). */
    private static function run(string $sql, string $from, string $to): PDOStatement
    {
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$from . ' 00:00:00', $to . ' 23:59:59']);
        return $stmt;
    }
}
