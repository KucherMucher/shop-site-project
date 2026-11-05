<?php if (!empty($flashes)): ?>
    <div class="flash-stack" data-flash-stack role="status" aria-live="polite">
        <?php foreach ($flashes as $flash): ?>
            <?php
            $type = $flash['type'] ?? 'info';
            $iconName = match ($type) {
                'success' => 'check-circle',
                'error' => 'alert',
                'warning' => 'alert',
                default => 'info',
            };
            ?>
            <div class="flash flash--<?= e($type) ?>" data-flash>
                <span class="flash__icon"><?= icon($iconName, '', 19) ?></span>
                <p><?= e($flash['message']) ?></p>
                <button type="button" class="flash__close" data-flash-close aria-label="<?= e(t('nav.close')) ?>"><?= icon('close', '', 16) ?></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
