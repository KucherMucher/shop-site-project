<?php
$pageStyles = ['account.css'];
require BASE_PATH . '/src/Views/partials/head.php';
require BASE_PATH . '/src/Views/partials/header.php';
$nav = $activeNav ?? 'overview';
$accountUser = $currentUser;
?>
<main id="main" class="account-page">
    <div class="container">
        <div class="account-banner">
            <div class="account-banner__identity">
                <span class="avatar avatar--lg"><?= e(initials($accountUser['name'])) ?></span>
                <div>
                    <span class="eyebrow"><?= icon('user', '', 13) ?> <?= e(t('account.title')) ?></span>
                    <h1><?= e(t('account.hello', ['name' => explode(' ', $accountUser['name'])[0]])) ?></h1>
                    <p><?= e(t('account.member_since', ['date' => format_date($accountUser['created_at'])])) ?></p>
                </div>
            </div>
            <div class="account-banner__actions">
                <a class="btn btn--ghost btn--sm" href="<?= url('/loja') ?>"><?= icon('grid', '', 16) ?> <?= e(t('nav.shop')) ?></a>
                <a class="btn btn--ghost btn--sm" href="<?= url('/suporte') ?>"><?= icon('headset', '', 16) ?> <?= e(t('nav.support')) ?></a>
            </div>
        </div>

        <div class="account-grid">
            <aside class="account-sidebar">
                <nav class="account-nav">
                    <a class="<?= $nav === 'overview' ? 'is-active' : '' ?>" href="<?= url('/conta') ?>"><?= icon('dashboard', '', 18) ?> <?= e(t('account.nav_overview')) ?></a>
                    <a class="<?= $nav === 'orders' ? 'is-active' : '' ?>" href="<?= url('/conta/encomendas') ?>"><?= icon('package', '', 18) ?> <?= e(t('account.nav_orders')) ?></a>
                    <a class="<?= $nav === 'profile' ? 'is-active' : '' ?>" href="<?= url('/conta/perfil') ?>"><?= icon('user', '', 18) ?> <?= e(t('account.nav_profile')) ?></a>
                    <a class="<?= $nav === 'addresses' ? 'is-active' : '' ?>" href="<?= url('/conta/moradas') ?>"><?= icon('map-pin', '', 18) ?> <?= e(t('account.nav_addresses')) ?></a>
                    <a href="<?= url('/suporte') ?>"><?= icon('chat', '', 18) ?> <?= e(t('account.nav_support')) ?></a>
                </nav>
                <form action="<?= url('/logout') ?>" method="post" class="account-sidebar__logout">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--ghost btn--sm btn--block"><?= icon('logout', '', 16) ?> <?= e(t('nav.logout')) ?></button>
                </form>
            </aside>

            <div class="account-content">
                <?= $content ?>
            </div>
        </div>
    </div>
</main>
<?php require BASE_PATH . '/src/Views/partials/footer.php'; ?>
<?php require BASE_PATH . '/src/Views/partials/scripts.php'; ?>
