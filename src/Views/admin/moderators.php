<?php
/** @var array $moderators */
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('shield', '', 13) ?> <?= e(t('admin.moderators')) ?></span>
        <h1><?= e(t('admin.moderators')) ?></h1>
        <p><?= e(t('admin.moderators_subtitle')) ?></p>
    </div>
    <span class="admin-chip"><?= icon('shield-check', '', 14) ?> <?= count($moderators) ?> <?= e(t('common.results')) ?></span>
</div>

<div class="admin-grid admin-grid--moderators">
    <section class="admin-card admin-card--flush">
        <?php if ($moderators): ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th><?= e(t('common.name')) ?></th><th><?= e(t('common.status')) ?></th><th><?= e(t('admin.chats_handled')) ?></th><th><?= e(t('admin.joined')) ?></th><th><?= e(t('admin.last_login')) ?></th><th><?= e(t('common.actions')) ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($moderators as $moderator): ?>
                            <tr>
                                <td data-label="<?= e(t('common.name')) ?>">
                                    <div class="table-user">
                                        <span class="avatar avatar--sm avatar--amber"><?= e(initials($moderator['name'])) ?></span>
                                        <span><strong><?= e($moderator['name']) ?></strong><small><?= e($moderator['email']) ?></small></span>
                                    </div>
                                </td>
                                <td data-label="<?= e(t('common.status')) ?>"><span class="status-pill <?= $moderator['status'] === 'active' ? 'status-green' : 'status-red' ?>"><?= e(t('admin.' . ($moderator['status'] === 'active' ? 'active' : 'suspended'))) ?></span></td>
                                <td data-label="<?= e(t('admin.chats_handled')) ?>"><strong><?= (int) $moderator['chats_handled'] ?></strong></td>
                                <td data-label="<?= e(t('admin.joined')) ?>"><?= e(format_date($moderator['created_at'])) ?></td>
                                <td data-label="<?= e(t('admin.last_login')) ?>"><?= e($moderator['last_login_at'] ? format_date($moderator['last_login_at'], true) : t('admin.na')) ?></td>
                                <td data-label="<?= e(t('common.actions')) ?>" class="admin-table__actions">
                                    <?php if ((int) $moderator['id'] === (int) $currentUser['id']): ?>
                                        <span class="admin-chip admin-chip--muted"><?= e(t('admin.na')) ?></span>
                                    <?php else: ?>
                                        <form action="<?= url('/admin/moderadores/' . $moderator['id'] . '/alternar') ?>" method="post" data-confirm="<?= e($moderator['status'] === 'active' ? t('admin.revoke_confirm') : t('admin.activate_confirm')) ?>">
                                            <?= csrf_field() ?>
                                            <button class="btn btn--ghost btn--sm <?= $moderator['status'] === 'active' ? 'btn--danger-ghost' : '' ?>" type="submit">
                                                <?= icon($moderator['status'] === 'active' ? 'lock' : 'check-circle', '', 14) ?>
                                                <?= e($moderator['status'] === 'active' ? t('admin.revoke') : t('admin.activate')) ?>
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
            <div class="admin-empty admin-empty--lg"><?= icon('shield', '', 34) ?><p><?= e(t('common.no_results')) ?></p></div>
        <?php endif; ?>
    </section>

    <aside class="admin-card admin-card--form">
        <div class="admin-card__head">
            <div>
                <span class="eyebrow"><?= icon('plus', '', 13) ?> <?= e(t('admin.system')) ?></span>
                <h2><?= e(t('admin.new_moderator')) ?></h2>
            </div>
        </div>
        <form action="<?= url('/admin/moderadores') ?>" method="post" class="admin-form">
            <?= csrf_field() ?>
            <?php if (error('form')): ?><div class="alert alert--error"><?= icon('alert', '', 16) ?> <?= e(error('form')) ?></div><?php endif; ?>
            <label class="field">
                <span><?= e(t('admin.moderator_name')) ?> *</span>
                <input type="text" name="name" value="<?= e(old('name')) ?>" required autocomplete="name" class="<?= error('name') ? 'is-invalid' : '' ?>">
                <?php if (error('name')): ?><small class="field__error"><?= e(error('name')) ?></small><?php endif; ?>
            </label>
            <label class="field">
                <span><?= e(t('admin.moderator_email')) ?> *</span>
                <input type="email" name="email" value="<?= e(old('email')) ?>" required autocomplete="email" class="<?= error('email') ? 'is-invalid' : '' ?>">
                <?php if (error('email')): ?><small class="field__error"><?= e(error('email')) ?></small><?php endif; ?>
            </label>
            <label class="field">
                <span><?= e(t('admin.moderator_password')) ?> *</span>
                <span class="field__control">
                    <?= icon('lock', '', 16) ?>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password" class="<?= error('password') ? 'is-invalid' : '' ?>" data-password-input>
                    <button type="button" class="field__reveal" data-password-toggle aria-label="<?= e(t('common.show_password')) ?>"><?= icon('eye', '', 15) ?></button>
                </span>
                <?php if (error('password')): ?><small class="field__error"><?= e(error('password')) ?></small><?php else: ?><small class="field__hint"><?= e(t('auth.password_hint')) ?></small><?php endif; ?>
            </label>
            <div class="admin-form__note"><?= icon('shield-check', '', 15) ?> <?= e(t('admin.role_permissions')) ?>: <?= e(t('admin.approvals_only_admin')) ?></div>
            <button class="btn btn--primary btn--block" type="submit"><?= icon('plus', '', 16) ?> <?= e(t('admin.create_moderator')) ?></button>
        </form>
    </aside>
</div>
