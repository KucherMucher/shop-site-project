<?php
/** @var array $user */
?>
<div class="section-head section-head--account">
    <div>
        <span class="eyebrow"><?= icon('user', '', 13) ?> <?= e(t('account.nav_profile')) ?></span>
        <h2><?= e(t('account.profile_title')) ?></h2>
        <p><?= e(t('account.profile_subtitle')) ?></p>
    </div>
</div>

<div class="profile-layout">
    <section class="content-card">
        <div class="content-card__head">
            <span class="eyebrow"><?= icon('settings', '', 13) ?> <?= e(t('account.nav_profile')) ?></span>
            <h2><?= e(t('account.personal_data')) ?></h2>
        </div>

        <form action="<?= url('/conta/perfil') ?>" method="post" class="form-stack">
            <?= csrf_field() ?>
            <div class="form-grid">
                <label class="field">
                    <span><?= e(t('auth.name')) ?></span>
                    <input type="text" name="name" value="<?= e(old('name', $user['name'])) ?>" required autocomplete="name" class="<?= error('name') ? 'is-invalid' : '' ?>">
                    <?php if (error('name')): ?><small class="field__error"><?= e(error('name')) ?></small><?php endif; ?>
                </label>
                <label class="field">
                    <span><?= e(t('common.email')) ?></span>
                    <input type="email" value="<?= e($user['email']) ?>" disabled>
                    <small class="field__hint"><?= e(t('account.profile_email_locked')) ?></small>
                </label>
                <label class="field">
                    <span><?= e(t('common.phone')) ?></span>
                    <input type="tel" name="phone" value="<?= e(old('phone', $user['phone'] ?? '')) ?>" autocomplete="tel">
                </label>
                <label class="field">
                    <span><?= e(t('account.member_since', ['date' => format_date($user['created_at'])])) ?></span>
                    <input type="text" value="<?= e($user['role_name'] ?? '') ?>" disabled>
                </label>
            </div>

            <div class="form-divider"><span><?= e(t('account.change_password')) ?></span></div>

            <div class="form-grid">
                <label class="field field--full">
                    <span><?= e(t('account.current_password')) ?></span>
                    <span class="field__control">
                        <?= icon('lock', '', 17) ?>
                        <input type="password" name="current_password" autocomplete="current-password" class="<?= error('current_password') ? 'is-invalid' : '' ?>">
                    </span>
                    <?php if (error('current_password')): ?><small class="field__error"><?= e(error('current_password')) ?></small><?php endif; ?>
                </label>
                <label class="field">
                    <span><?= e(t('account.new_password')) ?></span>
                    <input type="password" name="new_password" minlength="8" autocomplete="new-password" class="<?= error('new_password') ? 'is-invalid' : '' ?>">
                    <?php if (error('new_password')): ?><small class="field__error"><?= e(error('new_password')) ?></small><?php else: ?><small class="field__hint"><?= e(t('auth.password_hint')) ?></small><?php endif; ?>
                </label>
                <label class="field">
                    <span><?= e(t('auth.confirm_password')) ?></span>
                    <input type="password" name="new_password_confirmation" autocomplete="new-password" class="<?= error('new_password_confirmation') ? 'is-invalid' : '' ?>">
                    <?php if (error('new_password_confirmation')): ?><small class="field__error"><?= e(error('new_password_confirmation')) ?></small><?php endif; ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary"><?= icon('check', '', 16) ?> <?= e(t('common.save')) ?></button>
            </div>
        </form>
    </section>

    <aside class="profile-aside">
        <div class="side-card side-card--profile">
            <span class="avatar avatar--xl"><?= e(initials($user['name'])) ?></span>
            <strong><?= e($user['name']) ?></strong>
            <small><?= e($user['email']) ?></small>
            <span class="status-pill status-green"><?= icon('check-circle', '', 14) ?> <?= e(t('common.active')) ?></span>
        </div>
        <div class="side-card">
            <h3><?= icon('shield-check', '', 17) ?> <?= e(t('account.privacy_title')) ?></h3>
            <p><?= e(t('account.privacy_text')) ?></p>
        </div>
        <div class="side-card side-card--flat side-card--summary">
            <div class="summary-meta"><span><?= e(t('account.member_since', ['date' => ''])) ?></span><strong><?= e(format_date($user['created_at'])) ?></strong></div>
            <div class="summary-meta"><span><?= e(t('auth.account_type')) ?></span><strong><?= e($user['role_name'] ?? '') ?></strong></div>
            <div class="summary-meta"><span><?= e(t('admin.last_login')) ?></span><strong><?= e(format_date($user['last_login_at'] ?? null, true)) ?></strong></div>
        </div>
    </aside>
</div>
