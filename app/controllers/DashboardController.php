<?php
// Dashboards for each role.

class DashboardController extends Controller
{
    /** GET / - send visitors to their dashboard, or to the login page. */
    public function index(): void
    {
        redirect(is_logged_in() ? dashboard_path($_SESSION['role']) : '/login');
    }

    /**
     * GET /admin/dashboard - all-time overview: headline counts, newest
     * requests, and statistics by category and priority.
     * Every number comes from COUNT()/SUM() queries in the models.
     */
    public function admin(): void
    {
        $totals = MaintenanceRequest::dashboardTotals();

        // [label, value, color, icon, link to the matching filtered list, small note]
        $cards = [
            ['Total requests', $totals['total'],       'stat-blue',  'bi-clipboard-data',  '/admin/requests', null],
            ['Pending',        $totals['pending'],     'stat-amber', 'bi-hourglass-split', '/admin/requests?status=Pending', null],
            ['In progress',    $totals['in_progress'], 'stat-cyan',  'bi-tools',           '/admin/requests?status=In+Progress', null],
            ['Completed',      $totals['completed'],   'stat-green', 'bi-check-circle',    '/admin/requests?status=Completed', null],
            ['Urgent',         $totals['urgent'],      'stat-red',   'bi-exclamation-octagon', '/admin/requests?priority=Urgent',
                $totals['urgent_open'] . ' still open'],
        ];

        $this->view('admin/dashboard', [
            'pageTitle'  => 'Admin Dashboard',
            'activePage' => 'dashboard',
            'cards'      => $cards,
            'total'      => $totals['total'],
            'recent'     => MaintenanceRequest::recent(5),
            'byCategory' => Report::breakdown('category', Report::ALL_TIME_FROM, Report::ALL_TIME_TO),
            'byPriority' => Report::breakdown('priority', Report::ALL_TIME_FROM, Report::ALL_TIME_TO),
        ]);
    }

    /** GET /requester/dashboard - totals for the logged-in requester's own requests. */
    public function requester(): void
    {
        $counts = MaintenanceRequest::countByStatus((int) $_SESSION['user_id']);

        $cards = [
            ['My requests', array_sum($counts),          'stat-blue',  'bi-clipboard-data'],
            ['Pending',     $counts['Pending'] ?? 0,     'stat-amber', 'bi-hourglass-split'],
            ['In progress', $counts['In Progress'] ?? 0, 'stat-cyan',  'bi-tools'],
            ['Completed',   $counts['Completed'] ?? 0,   'stat-green', 'bi-check-circle'],
        ];

        $this->view('requester/dashboard', [
            'pageTitle'  => 'Dashboard',
            'activePage' => 'dashboard',
            'cards'      => $cards,
        ]);
    }
}
