        <?php // Bottom of every logged-in page: closes what header.php opened. ?>
    </main>

    <footer class="app-footer">
        &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/script.js') ?>"></script>
</body>
</html>
