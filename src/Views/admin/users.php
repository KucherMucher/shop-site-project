<?php
/** @var array $users */
/** @var array $filters */
$roles = ['user' => t('admin.role_user'), 'company' => t('admin.role_company'), 'moderator' => t('admin.role_moderator'), 'admin' => t('admin.role_admin')];
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('users', '', 13) ?> <?= e(t('admin.users')) ?></span>
        <h1><?= e(t('admin.users')) ?></h1>
        <p><?= e(t('admin.users_subtitle')) ?></p>
    </div>
    <span class="admin-chip"><?= icon('users', '', 14) ?> <?= count($users) ?> <?= e(t('common.results')) ?></span>
</div>

<form class="admin-filters" method="get" action="<?= url('/admin/utilizadores') ?>">
    <label class="admin-search">
        <?= icon('search', '', 16) ?>
        <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(t('admin.search_placeholder')) ?>">
    </label>
    <select name="role">
        <option value=""><?= e(t('admin.all_roles')) ?></option>
        <?php foreach ($roles as $slug => $label): ?>
            <option value="<?= e($slug) ?>" <?= $filters['role'] === $slug ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status">
        <option value=""><?= e(t('admin.all_statuses')) ?></option>
        <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>><?= e(t('admin.active')) ?></option>
        <option value="suspended" <?= $filters['status'] === 'suspended' ? 'selected' : '' ?>><?= e(t('admin.suspended')) ?></option>
        <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>><?= e(t('admin.pending')) ?></option>
    </select>
    <button class="btn btn--primary btn--sm" type="submit"><?= icon('filter', '', 15) ?> <?= e(t('admin.filter')) ?></button>
    <a class="btn btn--ghost btn--sm" href="<?= url('/admin/utilizadores') ?>"><?= e(t('admin.reset')) ?></a>
</form>

<div class="admin-card admin-card--flush">
    <?php if ($users): ?>
        <div class="table-wrap">
            <table class="admin-table admin-table--users">
                <thead>
                    <tr>
                        <th><?= e(t('common.name')) ?></th>
                        <th><?= e(t('admin.role')) ?></th>
                        <th><?= e(t('common.status')) ?></th>
                        <th><?= e(t('admin.orders_count')) ?></th>
                        <th><?= e(t('admin.total_spent')) ?></th>
                        <th><?= e(t('admin.reviews_count')) ?></th>
                        <th><?= e(t('admin.joined')) ?></th>
                        <th><?= e(t('common.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php $isAdminUser = $user['role_slug'] === 'admin'; ?>
                        <tr>
                            <td data-label="<?= e(t('common.name')) ?>">
                                <div class="table-user">
                                    <span class="avatar avatar--sm"><?= e(initials($user['name'])) ?></span>
                                    <span>
                                        <strong><?= e($user['name']) ?></strong>
                                        <small><?= e($user['email']) ?></small>
                                        <?php if (!empty($user['company_name'])): ?><small class="table-user__company"><?= icon('building', '', 11) ?> <?= e($user['company_name']) ?></small><?php endif; ?>
                                    </span>
                                </div>
                            </td>
                            <td data-label="<?= e(t('admin.role')) ?>">
                                <span class="role-chip role-chip--<?= e($user['role_slug']) ?>"><?= e($roles[$user['role_slug']] ?? $user['role_slug']) ?></span>
                            </td>
                            <td data-label="<?= e(t('common.status')) ?>">
                                <span class="status-pill <?= $user['status'] === 'active' ? 'status-green' : ($user['status'] === 'pending' ? 'status-amber' : 'status-red') ?>"><?= e(t('admin.' . ($user['status'] === 'active' ? 'active' : ($user['status'] === 'pending' ? 'pending' : 'suspended')))) ?></span>
                            </td>
                            <td data-label="<?= e(t('admin.orders_count')) ?>"><strong><?= (int) $user['orders_count'] ?></strong><small class="muted"><?= (int) $user['products_bought'] ?> <?= e(t('admin.products_bought')) ?></small></td>
                            <td data-label="<?= e(t('admin.total_spent')) ?>"><strong><?= money((float) $user['total_spent']) ?></strong></td>
                            <td data-label="<?= e(t('admin.reviews_count')) ?>"><?= (int) $user['reviews_count'] ?></td>
                            <td data-label="<?= e(t('admin.joined')) ?>"><?= e(format_date($user['created_at'])) ?><small class="muted"><?= e(t('admin.last_login')) ?>: <?= e($user['last_login_at'] ? format_date($user['last_login_at'], true) : t('admin.na')) ?></small></td>
                            <td data-label="<?= e(t('common.actions')) ?>" class="admin-table__actions">
                                <?php if ($isAdminUser): ?>
                                    <span class="admin-chip admin-chip--muted"><?= icon('lock', '', 12) ?> <?= e(t('admin.role_admin')) ?></span>
                                <?php else: ?>
                                    <form action="<?= url('/admin/utilizadores/' . $user['id'] . '/estado') ?>" method="post" data-confirm="<?= e($user['status'] === 'active' ? t('admin.suspend_confirm') : t('admin.activate_confirm')) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="status" value="<?= $user['status'] === 'active' ? 'suspended' : 'active' ?>">
                                        <button class="btn btn--ghost btn--sm <?= $user['status'] === 'active' ? 'btn--danger-ghost' : '' ?>" type="submit">
                                            <?= icon($user['status'] === 'active' ? 'lock' : 'check-circle', '', 14) ?>
                                            <?= e($user['status'] === 'active' ? t('admin.suspend') : t('admin.activate')) ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="admin-empty admin-empty--lg"><?= icon('users', '', 34) ?><p><?= e(t('common.no_results')) ?></p><a class="btn btn--ghost btn--sm" href="<?= url('/admin/utilizadores') ?>"><?= e(t('admin.reset')) ?></a></div>
    <?php endif; ?>
</div>
