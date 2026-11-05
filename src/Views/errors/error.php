<?php
/** @var int $status */
/** @var string|null $message */
/** @var \Throwable|null $debug */
$status = (int) ($status ?? 404);
$titles = [403 => t('errors.403_title'), 404 => t('errors.404_title'), 419 => t('errors.419_title'), 429 => t('errors.429_title'), 500 => t('errors.500_title')];
$texts = [
    403 => t('errors.403_text'),
    404 => t('errors.404_text'),
    419 => t('errors.419_text'),
    429 => t('errors.429_text'),
    500 => t('errors.500_text'),
];
$heading = $titles[$status] ?? t('errors.500_title');
$body = $message ?: ($texts[$status] ?? t('errors.500_text'));
$codeLabel = match ($status) {
    404 => '404',
    403 => '403',
    419 => '419',
    429 => '429',
    default => '500',
};
?>
<section class="section section--error">
    <div class="container">
        <div class="error-page" data-reveal>
            <div class="error-page__art" aria-hidden="true">
                <span class="error-page__code"><?= e($codeLabel) ?></span>
                <span class="error-page__ghost"><?= icon('ghost', '', 74) ?></span>
            </div>
            <span class="eyebrow"><?= icon('alert', '', 13) ?> TRIVEX · <?= e($status) ?></span>
            <h1><?= e($heading) ?></h1>
            <p><?= e($body) ?></p>
            <div class="error-page__actions">
                <a class="btn btn--primary" href="<?= url('/') ?>"><?= icon('home', '', 16) ?> <?= e(t('checkout.back_home')) ?></a>
                <a class="btn btn--ghost" href="<?= url('/loja') ?>"><?= icon('grid', '', 16) ?> <?= e(t('nav.shop')) ?></a>
                <a class="btn btn--ghost" href="<?= url('/suporte') ?>"><?= icon('headset', '', 16) ?> <?= e(t('support.title')) ?></a>
            </div>

            <?php if (!empty($debug) && $debug instanceof \Throwable): ?>
                <details class="error-page__debug">
                    <summary><?= icon('settings', '', 14) ?> Debug: <?= e(get_class($debug)) ?></summary>
                    <p><strong><?= e($debug->getMessage()) ?></strong></p>
                    <p class="muted"><?= e($debug->getFile()) ?>:<?= (int) $debug->getLine() ?></p>
                    <pre><?= e($debug->getTraceAsString()) ?></pre>
                </details>
            <?php endif; ?>
        </div>
    </div>
</section>
