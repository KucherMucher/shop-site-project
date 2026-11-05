<?php $pageScripts = $pageScripts ?? []; ?>
<?php require BASE_PATH . '/src/Views/partials/flash.php'; ?>
<?php require BASE_PATH . '/src/Views/partials/chat-widget.php'; ?>
<script src="<?= asset('js/app.js') ?>" defer></script>
<?php if (in_array('chat.js', $pageScripts, true)): ?>
    <script src="<?= asset('js/chat.js') ?>" defer></script>
<?php endif; ?>
<?php foreach ($pageScripts as $script): ?>
    <?php if ($script !== 'chat.js'): ?>
        <script src="<?= asset('js/' . $script) ?>" defer></script>
    <?php endif; ?>
<?php endforeach; ?>
</body>
</html>
