<?php
// Reports (admin only): summary for a date range, printable page, CSV export.

class ReportController extends Controller
{
    /** GET /admin/reports?from=Y-m-d&to=Y-m-d - the report page. Default: this month. */
    public function index(): void
    {
        [$from, $to] = $this->rangeFromQuery();

        $this->view('admin/reports', [
            'pageTitle'     => 'Reports',
            'activePage'    => 'reports',
            'from'          => $from,
            'to'            => $to,
            'presets'       => $this->presets(),
            'summary'       => Report::summary($from, $to),
            'byStatus'      => Report::byStatus($from, $to),
            'byCategory'    => Report::breakdown('category', $from, $to),
            'byPriority'    => Report::breakdown('priority', $from, $to),
            'monthly'       => Report::monthly($from, $to),
            'topRequesters' => Report::topRequesters($from, $to),
        ]);
    }

    /** GET /admin/reports/export?from=&to= - download the range's requests as a CSV file (opens in Excel). */
    public function export(): void
    {
        [$from, $to] = $this->rangeFromQuery();
        $rows        = Report::requestsForExport($from, $to);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="maintenance-requests_' . $from . '_to_' . $to . '.csv"');
        header('Cache-Control: no-store');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");   // lets Excel read accented characters correctly

        fputcsv($out, [
            'Reference No.', 'Title', 'Category', 'Location', 'Priority', 'Status',
            'Requester', 'Department', 'Handled By', 'Remarks', 'Date Submitted', 'Date Completed',
        ], ',', '"', '');

        foreach ($rows as $row) {
            fputcsv($out, array_map([$this, 'safeCell'], array_values($row)), ',', '"', '');
        }

        fclose($out);
        exit;
    }

    /**
     * Read ?from= and ?to= (Y-m-d). Missing or invalid values fall back to this
     * month; a reversed range is swapped; future dates are capped at today.
     */
    private function rangeFromQuery(): array
    {
        $today = date('Y-m-d');
        $from  = $this->validDate($_GET['from'] ?? null) ?? date('Y-m-01');
        $to    = $this->validDate($_GET['to'] ?? null) ?? $today;

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        if ($to > $today) {
            $to = $today;
        }
        if ($from > $to) {
            $from = $to;
        }

        return [$from, $to];
    }

    /** Quick ranges shown as buttons: label => [from, to]. */
    private function presets(): array
    {
        $today = date('Y-m-d');

        return [
            'This month'   => [date('Y-m-01'), $today],
            'Last 30 days' => [date('Y-m-d', strtotime('-29 days')), $today],
            'This year'    => [date('Y-01-01'), $today],
            'All time'     => [Report::firstRequestDate() ?? $today, $today],
        ];
    }

    private function validDate($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $date = DateTime::createFromFormat('Y-m-d', $value);
        return ($date && $date->format('Y-m-d') === $value) ? $value : null;
    }

    /**
     * Stop spreadsheet programs from running text as a formula
     * (a cell starting with = + - @ could otherwise execute commands).
     */
    private function safeCell($value): string
    {
        $value = (string) ($value ?? '');
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
