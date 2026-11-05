<?php
$bodyClass = 'admin-body';
$pageStyles = ['admin.css'];
$pageScripts = ['admin.js', 'chat.js'];
require BASE_PATH . '/src/Views/partials/head.php';
$nav = $activeNav ?? 'dashboard';
$isAdmin = ($currentUser['role_slug'] ?? '') === 'admin';
$adminName = $currentUser['name'] ?? 'TRIVEX';
$unread = $navUnread ?? 0;
?>
<div class="admin-shell">
    <aside class="admin-sidebar" data-admin-sidebar>
        <div class="admin-brand">
            <a class="logo logo--light" href="<?= url('/admin') ?>">
                <span class="logo__mark">
                    <svg viewBox="0 0 40 40" width="34" height="34" aria-hidden="true"><path d="M20 3 36 34H20Z" fill="#4fe0ff"/><path d="M20 3 4 34h16Z" fill="#fff"/><path d="M12 34h16l-8-14Z" fill="#0b6bff"/></svg>
                </span>
                <span class="logo__text"><strong>TRIVEX</strong><small>backoffice</small></span>
            </a>
            <button class="icon-btn admin-sidebar__close" type="button" data-admin-close aria-label="<?= e(t('nav.close')) ?>"><?= icon('close', '', 20) ?></button>
        </div>

        <div class="admin-role">
            <span class="admin-role__icon"><?= icon($isAdmin ? 'shield-check' : 'shield', '', 18) ?></span>
            <span>
                <small><?= e(t('admin.current_status')) ?></small>
                <strong><?= e($isAdmin ? t('admin.role_admin') : t('admin.role_moderator')) ?></strong>
            </span>
        </div>

        <nav class="admin-nav">
            <span class="admin-nav__label"><?= e(t('admin.overview')) ?></span>
            <a class="<?= $nav === 'dashboard' ? 'is-active' : '' ?>" href="<?= url('/admin') ?>"><?= icon('dashboard', '', 18) ?> <span><?= e(t('admin.dashboard')) ?></span></a>

            <span class="admin-nav__label"><?= e(t('admin.catalog')) ?></span>
            <a class="<?= $nav === 'users' ? 'is-active' : '' ?>" href="<?= url('/admin/utilizadores') ?>"><?= icon('users', '', 18) ?> <span><?= e(t('admin.users')) ?></span></a>
            <a class="<?= $nav === 'stock' ? 'is-active' : '' ?>" href="<?= url('/admin/stock') ?>"><?= icon('box', '', 18) ?> <span><?= e(t('admin.stock')) ?></span></a>
            <a class="<?= $nav === 'orders' ? 'is-active' : '' ?>" href="<?= url('/admin/encomendas') ?>"><?= icon('package', '', 18) ?> <span><?= e(t('admin.orders')) ?></span></a>
            <a class="<?= $nav === 'companies' ? 'is-active' : '' ?>" href="<?= url('/admin/empresas') ?>"><?= icon('building', '', 18) ?> <span><?= e(t('admin.companies')) ?></span>
                <?php if (($navCompanies ?? 0) > 0): ?><em class="admin-badge"><?= (int) $navCompanies ?></em><?php endif; ?>
            </a>
            <a class="<?= $nav === 'proposals' ? 'is-active' : '' ?>" href="<?= url('/admin/propostas') ?>"><?= icon('tag', '', 18) ?> <span><?= e(t('admin.proposals')) ?></span>
                <?php if (($navProposals ?? 0) > 0): ?><em class="admin-badge"><?= (int) $navProposals ?></em><?php endif; ?>
            </a>

            <span class="admin-nav__label"><?= e(t('admin.system')) ?></span>
            <a class="<?= $nav === 'pages' ? 'is-active' : '' ?>" href="<?= url('/admin/paginas') ?>"><?= icon('file', '', 18) ?> <span><?= e(t('admin.pages')) ?></span></a>
            <a class="<?= $nav === 'chats' ? 'is-active' : '' ?>" href="<?= url('/admin/chats') ?>"><?= icon('chat', '', 18) ?> <span><?= e(t('admin.chats')) ?></span>
                <?php if (($navChats ?? 0) > 0): ?><em class="admin-badge admin-badge--live"><?= (int) $navChats ?></em><?php endif; ?>
            </a>
            <?php if ($isAdmin): ?>
                <a class="<?= $nav === 'approvals' ? 'is-active' : '' ?>" href="<?= url('/admin/aprovacoes') ?>"><?= icon('shield-check', '', 18) ?> <span><?= e(t('admin.approvals')) ?></span>
                    <?php if (($navPendingApprovals ?? 0) > 0): ?><em class="admin-badge"><?= (int) $navPendingApprovals ?></em><?php endif; ?>
                </a>
                <a class="<?= $nav === 'moderators' ? 'is-active' : '' ?>" href="<?= url('/admin/moderadores') ?>"><?= icon('shield', '', 18) ?> <span><?= e(t('admin.moderators')) ?></span></a>
                <a class="<?= $nav === 'mailbox' ? 'is-active' : '' ?>" href="<?= url('/admin/mailbox') ?>"><?= icon('mail', '', 18) ?> <span><?= e(t('admin.mailbox')) ?></span></a>
            <?php endif; ?>
            <a class="<?= $nav === 'notifications' ? 'is-active' : '' ?>" href="<?= url('/admin/notificacoes') ?>"><?= icon('bell', '', 18) ?> <span><?= e(t('admin.notifications')) ?></span>
                <?php if ($unread > 0): ?><em class="admin-badge"><?= (int) $unread ?></em><?php endif; ?>
            </a>
        </nav>

        <div class="admin-sidebar__bottom">
            <a class="admin-store-link" href="<?= url('/') ?>" target="_blank" rel="noopener"><?= icon('external', '', 16) ?> <?= e(t('admin.view_store')) ?></a>
            <div class="admin-user">
                <span class="avatar avatar--sm"><?= e(initials($adminName)) ?></span>
                <span class="admin-user__meta"><strong><?= e($adminName) ?></strong><small><?= e($isAdmin ? t('admin.role_admin') : t('admin.role_moderator')) ?></small></span>
                <form action="<?= url('/logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="icon-btn" title="<?= e(t('nav.logout')) ?>"><?= icon('logout', '', 17) ?></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="icon-btn admin-menu-toggle" type="button" data-admin-open aria-label="<?= e(t('nav.menu')) ?>"><?= icon('menu', '', 22) ?></button>
            <div class="admin-topbar__crumb">
                <span><?= e(t('admin.title')) ?></span>
                <?= icon('chevron-right', '', 13) ?>
                <strong><?= e(strip_tags($title ?? t('admin.dashboard'))) ?></strong>
            </div>
            <div class="admin-topbar__actions">
                <span class="admin-date"><?= icon('calendar', '', 15) ?> <?= e(format_date(date('Y-m-d'))) ?></span>
                <a class="icon-btn admin-bell" href="<?= url('/admin/notificacoes') ?>" aria-label="<?= e(t('notify.title')) ?>">
                    <?= icon('bell', '', 19) ?>
                    <?php if ($unread > 0): ?><span class="admin-bell__dot"><?= $unread > 9 ? '9+' : (int) $unread ?></span><?php endif; ?>
                </a>
                <a class="btn btn--ghost btn--sm admin-topbar__store" href="<?= url('/') ?>" target="_blank" rel="noopener"><?= icon('external', '', 15) ?> <?= e(t('admin.view_store')) ?></a>
            </div>
        </header>

        <main class="admin-content">
            <?= $content ?>
        </main>
    </div>
</div>
<div class="admin-overlay" data-admin-overlay></div>
<?php require BASE_PATH . '/src/Views/partials/flash.php'; ?>
<script src="<?= asset('js/app.js') ?>" defer></script>
<script src="<?= asset('js/admin.js') ?>" defer></script>
<script src="<?= asset('js/chat.js') ?>" defer></script>
</body>
</html>
