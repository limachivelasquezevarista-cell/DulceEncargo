</main>
<footer class="text-center text-muted small py-4">
    <?= APP_NAME ?> &middot; Sacaba, Cochabamba &middot; <?= date('Y') ?>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('js/validaciones.js') ?>"></script>
<?php foreach ((isset($scripts) ? $scripts : array()) as $js): ?>
<script src="<?= e($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
