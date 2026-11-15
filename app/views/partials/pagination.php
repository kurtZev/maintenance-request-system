<?php
// Page links under a list. Keeps the current search and filters.
// Variables: $page, $totalPages, $basePath (route of the list, e.g. '/admin/requests').
if ($totalPages <= 1) {
    return;
}

$pageUrl = function (int $number) use ($basePath): string {
    $query         = array_filter($_GET, 'is_string');
    $query['page'] = $number;
    return url($basePath) . '?' . http_build_query($query);
};

// Show at most 5 numbered links around the current page.
$first = max(1, min($page - 2, $totalPages - 4));
$last  = min($totalPages, $first + 4);
?>
<nav aria-label="Pages" class="d-flex justify-content-center justify-content-md-end p-3 border-top">
    <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($pageUrl($page - 1)) ?>" aria-label="Previous">&laquo;</a>
        </li>
        <?php for ($n = $first; $n <= $last; $n++): ?>
            <li class="page-item <?= $n === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= e($pageUrl($n)) ?>" <?= $n === $page ? 'aria-current="page"' : '' ?>><?= $n ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($pageUrl($page + 1)) ?>" aria-label="Next">&raquo;</a>
        </li>
    </ul>
</nav>
