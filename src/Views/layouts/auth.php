<?php
$bodyClass = 'auth-body';
$pageStyles = ['auth.css'];
require BASE_PATH . '/src/Views/partials/head.php';
?>
<div class="auth-shell">
    <aside class="auth-visual">
        <img class="auth-visual__image" src="<?= asset('img/products/bike-road-3.svg') ?>" alt="" aria-hidden="true">
        <div class="auth-visual__overlay"></div>
        <div class="auth-visual__content">
            <a class="logo logo--light" href="<?= url('/') ?>">
                <span class="logo__mark">
                    <svg viewBox="0 0 40 40" width="38" height="38" aria-hidden="true"><path d="M20 3 36 34H20Z" fill="#4fe0ff"/><path d="M20 3 4 34h16Z" fill="#fff"/><path d="M12 34h16l-8-14Z" fill="#0b6bff"/></svg>
                </span>
                <span class="logo__text"><strong>TRIVEX</strong><small>swim · bike · run</small></span>
            </a>

            <div class="auth-visual__copy">
                <span class="eyebrow eyebrow--light"><?= icon('sparkles', '', 14) ?> <?= e(t('brand.tagline')) ?></span>
                <h2><?= e(t('home.hero_title') !== 'home.hero_title' ? strip_tags(t('home.hero_title')) : 'Três disciplinas. Um só objetivo.') ?></h2>
                <p><?= e(t('brand.about')) ?></p>
                <ul class="auth-benefits">
                    <li><?= icon('check', '', 16) ?> <?= e(t('account.orders_subtitle')) ?></li>
                    <li><?= icon('check', '', 16) ?> <?= e(t('product.review_only_users')) ?></li>
                    <li><?= icon('check', '', 16) ?> <?= e(t('common.free_shipping', ['amount' => money(60)])) ?></li>
                </ul>
            </div>

            <div class="auth-visual__footer">
                <div class="avatar-stack">
                    <span class="avatar">JS</span><span class="avatar">IC</span><span class="avatar">PS</span><span class="avatar avatar--more">+2k</span>
                </div>
                <p><?= e(t('home.hero_stat_2')) ?></p>
            </div>
        </div>
    </aside>

    <section class="auth-main">
        <div class="auth-toolbar">
            <a class="auth-back" href="<?= url('/') ?>"><?= icon('arrow-left', '', 16) ?> <?= e(t('product.breadcrumb_home')) ?></a>
            <div class="auth-toolbar__actions">
                <div class="locale-switch">
                    <a class="<?= locale() === 'pt' ? 'is-active' : '' ?>" href="<?= url('/idioma/pt') ?>">PT</a><span></span><a class="<?= locale() === 'en' ? 'is-active' : '' ?>" href="<?= url('/idioma/en') ?>">EN</a>
                </div>
                <button class="icon-btn icon-btn--border" type="button" data-theme-toggle aria-label="<?= e(t('nav.theme')) ?>">
                    <?= icon('sun', 'theme-sun', 17) ?><?= icon('moon', 'theme-moon', 17) ?>
                </button>
            </div>
        </div>
        <div class="auth-card">
            <?= $content ?>
        </div>
        <p class="auth-legal">© <?= date('Y') ?> TRIVEX · <?= e(t('footer.demo')) ?></p>
    </section>
</div>
<?php require BASE_PATH . '/src/Views/partials/flash.php'; ?>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
