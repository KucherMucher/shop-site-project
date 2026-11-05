<?php
/** @var string|null $email */
/** @var bool $invalid */
/** @var string|null $devLink */
?>
<div class="auth-card__head">
    <span class="eyebrow"><?= icon('mail', '', 13) ?> <?= e(t('auth.verify_title')) ?></span>
    <h1><?= e($invalid ? t('auth.invalid_token') : t('auth.verify_title')) ?></h1>
    <p>
        <?php if ($invalid): ?>
            <?= e(t('auth.verify_retry_text')) ?>
        <?php elseif ($email): ?>
            <?= e(t('auth.verify_text', ['email' => $email])) ?>
        <?php else: ?>
            <?= e(t('auth.verify_generic_text')) ?>
        <?php endif; ?>
    </p>
</div>

<?php if ($invalid): ?>
    <div class="alert alert--error"><?= icon('alert', '', 18) ?> <?= e(t('auth.invalid_token')) ?></div>
<?php else: ?>
    <div class="verify-illustration" aria-hidden="true">
        <span class="verify-illustration__ring"></span>
        <?= icon('mail', '', 46) ?>
        <span class="verify-illustration__dot"><?= icon('check', '', 13) ?></span>
    </div>

    <div class="verify-steps">
        <div class="verify-step"><b>01</b><span><?= e(t('auth.verify_step_1')) ?></span></div>
        <div class="verify-step"><b>02</b><span><?= e(t('auth.verify_step_2')) ?></span></div>
        <div class="verify-step"><b>03</b><span><?= e(t('auth.verify_step_3')) ?></span></div>
    </div>
<?php endif; ?>

<?php if (!empty($devLink)): ?>
    <div class="dev-link">
        <strong><?= e(t('auth.dev_link')) ?></strong>
        <a href="<?= e($devLink) ?>"><?= e($devLink) ?></a>
    </div>
<?php endif; ?>

<form action="<?= url('/verificar-email/reenviar') ?>" method="post" class="auth-form auth-form--compact">
    <?= csrf_field() ?>
    <label class="field">
        <span><?= e(t('common.email')) ?></span>
        <span class="field__control">
            <?= icon('mail', '', 17) ?>
            <input type="email" name="email" value="<?= e($email ?? old('email')) ?>" required autocomplete="email" placeholder="nome@exemplo.pt">
        </span>
    </label>
    <button type="submit" class="btn btn--primary btn--block"><?= icon('refresh', '', 16) ?> <?= e(t('auth.resend')) ?></button>
</form>

<div class="auth-switch">
    <p><?= e(t('auth.has_account')) ?> <a href="<?= url('/login') ?>"><?= e(t('nav.login')) ?></a></p>
    <p><?= e(t('auth.no_account')) ?> <a href="<?= url('/registo') ?>"><?= e(t('nav.register')) ?></a></p>
</div>
