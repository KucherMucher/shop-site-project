<?php
/** @var array $products */
/** @var int $maxDiscount */
/** @var int $promoEnd */
$page = $page ?? null;
$count = count($products);
?>
<section class="promo-hero">
    <div class="promo-hero__art" aria-hidden="true">
        <span class="promo-moon promo-moon--large"></span>
        <span class="promo-ghost promo-ghost--hero"><?= icon('ghost', '', 240) ?></span>
        <span class="promo-bat promo-bat--hero">🦇</span>
        <span class="promo-bat promo-bat--hero2">🦇</span>
        <span class="promo-pumpkin promo-pumpkin--hero">🎃</span>
        <span class="promo-fog"></span>
    </div>
    <div class="container promo-hero__inner">
        <div class="promo-hero__copy">
            <span class="eyebrow eyebrow--light"><?= icon('ghost', '', 14) ?> <?= e(t('promo.eyebrow')) ?></span>
            <h1><?= e(t('promo.title')) ?></h1>
            <p><?= e(t('promo.subtitle')) ?></p>
            <div class="promo-hero__badges">
                <span class="promo-pill"><?= icon('tag', '', 15) ?> <?= e(t('promo.count', ['count' => $count])) ?></span>
                <span class="promo-pill"><?= icon('sparkles', '', 15) ?> <?= e(t('promo.spooky')) ?></span>
            </div>
            <a class="btn btn--accent btn--lg" href="#deals"><?= icon('ghost', '', 18) ?> <?= e(t('promo.cta')) ?></a>
        </div>
        <div class="promo-hero__countdown">
            <span class="eyebrow eyebrow--light"><?= icon('clock', '', 13) ?> <?= e(t('promo.countdown_title')) ?></span>
            <div class="countdown" data-countdown data-deadline="<?= (int) $promoEnd ?>">
                <div><strong data-days>00</strong><span><?= e(t('promo.days')) ?></span></div>
                <em>:</em>
                <div><strong data-hours>00</strong><span><?= e(t('promo.hours')) ?></span></div>
                <em>:</em>
                <div><strong data-minutes>00</strong><span><?= e(t('promo.minutes')) ?></span></div>
                <em>:</em>
                <div><strong data-seconds>00</strong><span><?= e(t('promo.seconds')) ?></span></div>
            </div>
            <p><?= e(t('promo.ends', ['date' => format_date(date('Y-m-d', $promoEnd))])) ?></p>
        </div>
    </div>
</section>

<section class="promo-strip">
    <div class="container promo-strip__inner">
        <span><?= icon('ghost', '', 18) ?> <?= e(t('promo.trick')) ?> <?= e(t('promo.trick_text')) ?></span>
        <strong><?= e(t('promo.products_subtitle')) ?></strong>
    </div>
</section>

<section class="section section--promo" id="deals">
    <div class="container">
        <div class="section-head section-head--spooky">
            <div>
                <span class="eyebrow"><?= icon('tag', '', 13) ?> <?= e(t('promo.products_title')) ?></span>
                <h2><?= e(t('promo.title')) ?> <span class="spooky-accent">−<?= (int) $maxDiscount ?>%</span></h2>
                <p><?= e(t('promo.products_subtitle')) ?></p>
            </div>
            <a class="link-arrow" href="<?= url('/loja') ?>"><?= e(t('nav.shop')) ?> <?= icon('arrow-right', '', 16) ?></a>
        </div>

        <?php if ($products): ?>
            <div class="product-grid product-grid--4">
                <?php $halloween = true; ?>
                <?php foreach ($products as $product): ?>
                    <?php require BASE_PATH . '/src/Views/partials/product-card.php'; ?>
                <?php endforeach; ?>
                <?php $halloween = false; ?>
            </div>
        <?php else: ?>
            <div class="empty-state empty-state--spooky">
                <span class="empty-state__icon"><?= icon('ghost', '', 40) ?></span>
                <h2><?= e(t('promo.empty')) ?></h2>
                <a class="btn btn--primary" href="<?= url('/loja') ?>"><?= e(t('nav.shop')) ?></a>
            </div>
        <?php endif; ?>

        <div class="promo-terms">
            <?= icon('info', '', 15) ?>
            <p><?= e(t('promo.disclaimer')) ?></p>
        </div>
    </div>
</section>

<?php if ($page): ?>
    <section class="section">
        <div class="container">
            <div class="editorial editorial--reverse">
                <div class="editorial__copy" data-reveal>
                    <span class="eyebrow"><?= icon('info', '', 13) ?> <?= e(localized($page, 'title')) ?></span>
                    <h2><?= e(t('promo.spooky')) ?></h2>
                    <p><?= e(localized($page, 'content')) ?></p>
                    <a class="btn btn--primary" href="<?= url('/loja') ?>"><?= e(t('nav.all')) ?> <?= icon('arrow-right', '', 16) ?></a>
                </div>
                <div class="editorial__media editorial__media--spooky" data-reveal>
                    <img src="<?= asset('img/categories/equipment.svg') ?>" alt="" loading="lazy">
                    <div class="editorial__badge">
                        <strong>31</strong>
                        <span>OCT</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
