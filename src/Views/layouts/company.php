<?php
$pageStyles = ['company.css'];
require BASE_PATH . '/src/Views/partials/head.php';
require BASE_PATH . '/src/Views/partials/header.php';
$nav = $companyNav ?? 'dashboard';
?>
<main id="main" class="company-page">
    <div class="container">
        <div class="company-banner">
            <div class="company-banner__identity">
                <span class="company-avatar"><?= e(initials($company['name'] ?? 'TRIVEX')) ?></span>
                <div>
                    <span class="eyebrow"><?= icon('building', '', 13) ?> <?= e(t('nav.company')) ?></span>
                    <h1><?= e($company['name'] ?? t('company.panel_title')) ?></h1>
                    <p><?= e(t('company.welcome', ['name' => explode(' ', $currentUser['name'])[0]])) ?> · <?= e(t('company.since', ['date' => format_date($company['created_at'] ?? null)])) ?></p>
                </div>
            </div>
            <?php
            $companyStatus = $company['status'] ?? 'pending';
            $companyStatusClass = match ($companyStatus) {
                'approved' => 'status-green',
                'rejected' => 'status-red',
                default => 'status-amber',
            };
            $companyStatusIcon = match ($companyStatus) {
                'approved' => 'check-circle',
                'rejected' => 'alert',
                default => 'clock',
            };
            ?>
            <span class="status-pill <?= $companyStatusClass ?>"><?= icon($companyStatusIcon, '', 15) ?> <?= e(t('company.status_' . $companyStatus)) ?></span>
        </div>

        <nav class="company-nav">
            <a class="<?= $nav === 'dashboard' ? 'is-active' : '' ?>" href="<?= url('/empresa/painel') ?>"><?= icon('dashboard', '', 17) ?> <?= e(t('company.overview')) ?></a>
            <a class="<?= $nav === 'products' ? 'is-active' : '' ?>" href="<?= url('/empresa/produtos') ?>"><?= icon('box', '', 17) ?> <?= e(t('company.products')) ?></a>
            <a class="<?= $nav === 'new' ? 'is-active' : '' ?>" href="<?= url('/empresa/produtos/novo') ?>"><?= icon('plus', '', 17) ?> <?= e(t('company.new_product')) ?></a>
            <span class="company-nav__spacer"></span>
            <a href="<?= url('/') ?>" target="_blank" rel="noopener"><?= icon('external', '', 16) ?> <?= e(t('admin.view_store')) ?></a>
        </nav>

        <?= $content ?>
    </div>
</main>
<?php require BASE_PATH . '/src/Views/partials/footer.php'; ?>
<?php require BASE_PATH . '/src/Views/partials/scripts.php'; ?>
