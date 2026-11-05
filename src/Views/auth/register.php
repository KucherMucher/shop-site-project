<?php
/** @var string $type */
$isCompany = $type === 'company';
?>
<div class="auth-card__head">
    <span class="eyebrow"><?= icon('sparkles', '', 13) ?> TRIVEX</span>
    <h1><?= e($isCompany ? t('auth.register_company_title') : t('auth.register_title')) ?></h1>
    <p><?= e($isCompany ? t('auth.register_company_subtitle') : t('auth.register_subtitle')) ?></p>
</div>

<div class="auth-tabs" role="tablist">
    <a class="auth-tab <?= !$isCompany ? 'is-active' : '' ?>" href="<?= url('/registo') ?>"><?= icon('user', '', 16) ?> <?= e(t('auth.register_user')) ?></a>
    <a class="auth-tab <?= $isCompany ? 'is-active' : '' ?>" href="<?= url('/registo?tipo=company') ?>"><?= icon('building', '', 16) ?> <?= e(t('auth.register_company')) ?></a>
</div>

<form action="<?= url('/registo') ?>" method="post" class="auth-form" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="account_type" value="<?= e($type) ?>">

    <?php if (error('form')): ?>
        <div class="alert alert--error"><?= icon('alert', '', 17) ?> <?= e(error('form')) ?></div>
    <?php endif; ?>

    <div class="form-grid">
        <label class="field <?= $isCompany ? '' : 'field--full' ?>">
            <span><?= e($isCompany ? t('auth.contact_name') : t('auth.name')) ?> *</span>
            <input type="text" name="name" value="<?= e(old('name')) ?>" required autocomplete="name" class="<?= error('name') ? 'is-invalid' : '' ?>">
            <?php if (error('name')): ?><small class="field__error"><?= e(error('name')) ?></small><?php endif; ?>
        </label>

        <?php if ($isCompany): ?>
            <label class="field">
                <span><?= e(t('auth.company_name')) ?> *</span>
                <input type="text" name="company_name" value="<?= e(old('company_name')) ?>" required class="<?= error('company_name') ? 'is-invalid' : '' ?>">
                <?php if (error('company_name')): ?><small class="field__error"><?= e(error('company_name')) ?></small><?php endif; ?>
            </label>
        <?php endif; ?>

        <label class="field <?= $isCompany ? '' : 'field--full' ?>">
            <span><?= e(t('common.email')) ?> *</span>
            <input type="email" name="email" value="<?= e(old('email')) ?>" required autocomplete="email" class="<?= error('email') ? 'is-invalid' : '' ?>">
            <?php if (error('email')): ?><small class="field__error"><?= e(error('email')) ?></small><?php endif; ?>
        </label>

        <?php if ($isCompany): ?>
            <label class="field">
                <span><?= e(t('auth.vat')) ?></span>
                <input type="text" name="vat" value="<?= e(old('vat')) ?>" placeholder="PT123456789">
            </label>
            <label class="field">
                <span><?= e(t('common.phone')) ?></span>
                <input type="tel" name="phone" value="<?= e(old('phone')) ?>" autocomplete="tel">
            </label>
            <label class="field field--full">
                <span><?= e(t('auth.website')) ?></span>
                <input type="url" name="website" value="<?= e(old('website')) ?>" placeholder="https://">
            </label>
            <label class="field field--full">
                <span><?= e(t('common.address')) ?></span>
                <input type="text" name="address" value="<?= e(old('address')) ?>" autocomplete="street-address">
            </label>
            <label class="field">
                <span><?= e(t('common.postal_code')) ?></span>
                <input type="text" name="postal_code" value="<?= e(old('postal_code')) ?>" autocomplete="postal-code">
            </label>
            <label class="field">
                <span><?= e(t('common.city')) ?></span>
                <input type="text" name="city" value="<?= e(old('city')) ?>" autocomplete="address-level2">
            </label>
        <?php endif; ?>

        <label class="field <?= $isCompany ? '' : 'field--full' ?>">
            <span><?= e(t('common.password')) ?> *</span>
            <span class="field__control">
                <?= icon('lock', '', 17) ?>
                <input type="password" name="password" required minlength="8" autocomplete="new-password" class="<?= error('password') ? 'is-invalid' : '' ?>" data-password-input>
                <button type="button" class="field__reveal" data-password-toggle aria-label="<?= e(t('common.show_password')) ?>"><?= icon('eye', '', 16) ?></button>
            </span>
            <?php if (error('password')): ?><small class="field__error"><?= e(error('password')) ?></small><?php else: ?><small class="field__hint"><?= e(t('auth.password_hint')) ?></small><?php endif; ?>
        </label>

        <label class="field <?= $isCompany ? '' : 'field--full' ?>">
            <span><?= e(t('auth.confirm_password')) ?> *</span>
            <span class="field__control">
                <?= icon('lock', '', 17) ?>
                <input type="password" name="password_confirmation" required autocomplete="new-password" class="<?= error('password_confirmation') ? 'is-invalid' : '' ?>">
            </span>
            <?php if (error('password_confirmation')): ?><small class="field__error"><?= e(error('password_confirmation')) ?></small><?php endif; ?>
        </label>
    </div>

    <label class="check-row check-row--bordered">
        <input type="checkbox" name="terms" value="1" <?= old('terms') ? 'checked' : '' ?> required>
        <span class="check-row__box"><?= icon('check', '', 12) ?></span>
        <span><?= e(t('auth.terms')) ?></span>
    </label>
    <?php if (error('terms')): ?><small class="field__error"><?= e(error('terms')) ?></small><?php endif; ?>

    <?php if ($isCompany): ?>
        <div class="auth-note"><?= icon('info', '', 16) ?> <?= e(t('auth.company_review_note')) ?></div>
    <?php endif; ?>

    <button type="submit" class="btn btn--primary btn--lg btn--block"><?= icon('sparkles', '', 17) ?> <?= e(t('auth.register_cta')) ?></button>
</form>

<div class="auth-switch">
    <p><?= e(t('auth.has_account')) ?> <a href="<?= url('/login') ?>"><?= e(t('nav.login')) ?></a></p>
</div>
