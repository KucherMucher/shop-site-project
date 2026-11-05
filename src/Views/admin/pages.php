<?php
/** @var array $pages */
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('file', '', 13) ?> <?= e(t('admin.pages')) ?></span>
        <h1><?= e(t('admin.pages')) ?></h1>
        <p><?= e(t('admin.pages_subtitle')) ?></p>
    </div>
    <span class="admin-chip"><?= icon('lock', '', 14) ?> <?= e(t('admin.home_locked')) ?></span>
</div>

<div class="admin-card admin-card--flush">
    <div class="table-wrap">
        <table class="admin-table admin-table--pages">
            <thead><tr><th><?= e(t('admin.page')) ?></th><th><?= e(t('admin.slug')) ?></th><th><?= e(t('admin.type')) ?></th><th><?= e(t('common.status')) ?></th><th><?= e(t('pages.updated', ['date' => ''])) ?></th><th><?= e(t('common.actions')) ?></th></tr></thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                    <?php $isHome = $page['slug'] === 'home'; ?>
                    <tr>
                        <td data-label="<?= e(t('admin.page')) ?>">
                            <div class="table-page">
                                <span class="table-page__icon"><?= icon($page['is_system'] ? 'file' : 'leaf', '', 17) ?></span>
                                <span><strong><?= e($page['title_pt']) ?></strong><small><?= e($page['title_en']) ?></small></span>
                            </div>
                        </td>
                        <td data-label="<?= e(t('admin.slug')) ?>"><code class="code-chip">/<?= e($page['slug']) ?></code></td>
                        <td data-label="<?= e(t('admin.type')) ?>"><span class="admin-chip <?= $page['is_system'] ? 'admin-chip--blue' : '' ?>"><?= e($page['is_system'] ? t('admin.system_page') : t('admin.custom_page')) ?></span></td>
                        <td data-label="<?= e(t('common.status')) ?>"><span class="status-pill <?= $page['active'] ? 'status-green' : 'status-red' ?>"><?= e($page['active'] ? t('admin.page_active') : t('admin.page_inactive')) ?></span></td>
                        <td data-label="<?= e(t('pages.updated', ['date' => ''])) ?>"><?= e(format_date($page['updated_at'])) ?></td>
                        <td data-label="<?= e(t('common.actions')) ?>" class="admin-table__actions">
                            <?php if ($isHome): ?>
                                <span class="admin-chip admin-chip--muted" title="<?= e(t('admin.home_locked')) ?>"><?= icon('lock', '', 13) ?> <?= e(t('admin.page_active')) ?></span>
                            <?php else: ?>
                                <form action="<?= url('/admin/paginas/' . $page['id'] . '/alternar') ?>" method="post" data-confirm="<?= e($page['active'] ? t('admin.page_inactive') : t('admin.page_active')) ?>?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn--ghost btn--sm <?= $page['active'] ? 'btn--danger-ghost' : '' ?>" type="submit"><?= icon($page['active'] ? 'eye' : 'check-circle', '', 14) ?> <?= e($page['active'] ? t('admin.page_inactive') : t('admin.page_active')) ?></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
