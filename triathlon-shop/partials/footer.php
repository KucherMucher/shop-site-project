<?php
declare(strict_types=1);
/** Shared page bottom. Set $scripts (array of extra JS files) before including. */
$scripts = $scripts ?? [];
?>
  <footer class="site-footer">
    <div class="wrap">Transition is a school project. Products and prices are demo data.</div>
  </footer>

  <script src="assets/js/common.js"></script>
<?php foreach ($scripts as $src): ?>
  <script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
</body>
</html>
