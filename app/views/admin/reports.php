<?php
// Reports (admin). Variables: $from, $to, $presets, $summary, $byStatus,
// $byCategory, $byPriority, $monthly, $topRequesters.
$total          = $summary['total'];
$open           = $summary['pending'] + $summary['in_progress'];
$completionRate = $total > 0 ? round($summary['completed'] / $total * 100) : 0;
$rangeText      = format_date($from, false) . ' – ' . format_date($to, false);
$query          = http_build_query(['from' => $from, 'to' => $to]);

$cards = [
    ['Requests submitted', $total,                 'stat-blue',  'bi-clipboard-data'],
    ['Completed',          $summary['completed'],  'stat-green', 'bi-check-circle'],
    ['Still open',         $open,                  'stat-amber', 'bi-hourglass-split'],
    ['Completion rate',    $completionRate . '%',  'stat-cyan',  'bi-graph-up-arrow'],
    ['Avg. time to complete', format_duration($summary['avg_hours']), 'stat-slate', 'bi-stopwatch'],
];

// Data for the charts, in the same colors as the status badges.
$statusColors = [
    'Pending'     => '#f0ad1e',
    'In Progress' => '#0d6efd',
    'Completed'   => '#198754',
    'Rejected'    => '#dc3545',
    'Cancelled'   => '#8a96a8',
];
$chartData = [
    'status' => [
        'labels' => array_keys($byStatus),
        'values' => array_values($byStatus),
        'colors' => array_values(array_intersect_key($statusColors, $byStatus)),
    ],
    'monthly' => [
        'labels'    => array_map(fn($m) => date('M Y', strtotime($m . '-01')), array_keys($monthly)),
        'submitted' => array_column($monthly, 'submitted'),
        'completed' => array_column($monthly, 'completed'),
    ],
];

/** Prints one breakdown table (category or priority). */
$breakdownTable = function (string $title, string $icon, array $rows, bool $badges) use ($total): void { ?>
    <div class="card h-100">
        <div class="card-header"><i class="bi <?= $icon ?> me-1"></i><?= e($title) ?></div>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th><?= e(str_replace('By ', '', $title)) ?></th>
                        <th class="text-end">Total</th>
                        <th>Share</th>
                        <th class="text-end">Open</th>
                        <th class="text-end">Done</th>
                        <th class="text-end">Avg. time</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php $share = $total > 0 ? round($row['total'] / $total * 100) : 0; ?>
                    <tr class="<?= $row['total'] === 0 ? 'text-muted' : '' ?>">
                        <td><?= $badges ? priority_badge($row['label']) : e($row['label']) ?></td>
                        <td class="text-end fw-semibold"><?= $row['total'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="share-bar flex-grow-1"><span style="width: <?= $share ?>%"></span></div>
                                <span class="small text-muted"><?= $share ?>%</span>
                            </div>
                        </td>
                        <td class="text-end"><?= $row['open'] ?></td>
                        <td class="text-end"><?= $row['completed'] ?></td>
                        <td class="text-end small text-nowrap"><?= format_duration($row['avg_hours']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php };
?>

<!-- Shown only on paper -->
<div class="print-only mb-4">
    <h1 class="h3 mb-1"><?= e(APP_NAME) ?>: Request Report</h1>
    <div>Requests submitted <?= e($rangeText) ?></div>
    <div class="small text-muted">Generated <?= date('M j, Y g:i A') ?> by <?= e($_SESSION['full_name']) ?></div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
    <div>
        <h1 class="h3 mb-0">Reports</h1>
        <p class="text-muted mb-0">Requests submitted <?= e($rangeText) ?></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-light border" data-action="print">
            <i class="bi bi-printer me-1"></i>Print
        </button>
        <a href="<?= url('/admin/reports/export') . '?' . e($query) ?>" class="btn btn-primary">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="get" action="<?= url('/admin/reports') ?>" class="row g-2 align-items-end">
            <div class="col-sm-4 col-lg-3">
                <label for="from" class="form-label small">From</label>
                <input type="date" class="form-control" id="from" name="from" value="<?= e($from) ?>" max="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-sm-4 col-lg-3">
                <label for="to" class="form-label small">To</label>
                <input type="date" class="form-control" id="to" name="to" value="<?= e($to) ?>" max="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-sm-4 col-lg-auto">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Apply</button>
            </div>
            <div class="col-lg d-flex flex-wrap gap-2 justify-content-lg-end">
                <?php foreach ($presets as $label => [$presetFrom, $presetTo]): ?>
                    <?php $isCurrent = $presetFrom === $from && $presetTo === $to; ?>
                    <a href="<?= url('/admin/reports') . '?' . e(http_build_query(['from' => $presetFrom, 'to' => $presetTo])) ?>"
                       class="btn btn-sm <?= $isCurrent ? 'btn-primary' : 'btn-outline-primary' ?>"
                       <?= $isCurrent ? 'aria-current="true"' : '' ?>><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($cards as [$label, $value, $color, $icon]): ?>
        <div class="col-xxl col-lg-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon <?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
                <div>
                    <div class="stat-value"><?= e($value) ?></div>
                    <div class="stat-label"><?= e($label) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($total === 0): ?>
    <div class="card">
        <div class="card-body table-empty">
            <i class="bi bi-bar-chart d-block fs-2 mb-2"></i>
            No requests were submitted in this period. Choose a wider range above.
        </div>
    </div>
<?php else: ?>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-pie-chart me-1"></i>By status</div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="statusChart" aria-label="Requests by status"></canvas></div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        <?php foreach ($byStatus as $status => $count): ?>
                            <span><?= status_badge($status) ?> <span class="small fw-semibold"><?= $count ?></span></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-graph-up me-1"></i>Requests per month</div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="monthlyChart" aria-label="Requests submitted and completed per month"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xxl-7">
            <?php $breakdownTable('By category', 'bi-tags', $byCategory, false); ?>
        </div>
        <div class="col-xxl-5">
            <?php $breakdownTable('By priority', 'bi-flag', $byPriority, true); ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-people me-1"></i>Most active requesters</div>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th class="text-end">Requests</th>
                        <th class="text-end">Completed</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($topRequesters as $i => $row): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="fw-medium"><?= e($row['full_name']) ?></td>
                        <td><?= $row['department'] !== null ? e($row['department']) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-end fw-semibold"><?= (int) $row['total'] ?></td>
                        <td class="text-end"><?= (int) $row['completed'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        if (typeof Chart === 'undefined') return;   // CDN unreachable: tables above still show the numbers

        const data = <?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        
        function getThemeConfig() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            return {
                textColor: isDark ? '#94a3b8' : '#66758c',
                borderColor: isDark ? '#131e36' : '#ffffff',
                gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)'
            };
        }

        const theme = getThemeConfig();
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
        Chart.defaults.color = theme.textColor;

        const statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: data.status.labels,
                datasets: [{ data: data.status.values, backgroundColor: data.status.colors, borderWidth: 2, borderColor: theme.borderColor }]
            },
            options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { display: false } } }
        });

        const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: data.monthly.labels,
                datasets: [
                    { label: 'Submitted', data: data.monthly.submitted, backgroundColor: '#0d6efd', borderRadius: 4 },
                    { label: 'Completed', data: data.monthly.completed, backgroundColor: '#20c997', borderRadius: 4 }
                ]
            },
            options: {
                maintainAspectRatio: false,
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        ticks: { precision: 0, color: theme.textColor },
                        grid: { color: theme.gridColor }
                    }, 
                    x: { 
                        ticks: { color: theme.textColor },
                        grid: { display: false } 
                    } 
                },
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: { color: theme.textColor }
                    } 
                }
            }
        });

        // Update charts dynamically when theme is switched
        window.addEventListener('themeChanged', function () {
            const newTheme = getThemeConfig();
            Chart.defaults.color = newTheme.textColor;

            statusChart.data.datasets[0].borderColor = newTheme.borderColor;
            statusChart.update();

            monthlyChart.options.scales.y.ticks.color = newTheme.textColor;
            monthlyChart.options.scales.y.grid.color = newTheme.gridColor;
            monthlyChart.options.scales.x.ticks.color = newTheme.textColor;
            monthlyChart.options.plugins.legend.labels.color = newTheme.textColor;
            monthlyChart.update();
        });
    })();
    </script>
<?php endif; ?>
