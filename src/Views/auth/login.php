<div class="auth-card__head">
    <span class="eyebrow"><?= icon('user', '', 13) ?> <?= e(t('nav.account')) ?></span>
    <h1><?= e(t('auth.login_title')) ?></h1>
    <p><?= e(t('auth.login_subtitle')) ?></p>
</div>

<form action="<?= url('/login') ?>" method="post" class="auth-form" novalidate>
    <?= csrf_field() ?>

    <?php if (error('email') && !error('password')): ?>
        <div class="alert alert--error"><?= icon('alert', '', 17) ?> <?= e(error('email')) ?></div>
    <?php endif; ?>

    <label class="field">
        <span><?= e(t('common.email')) ?></span>
        <span class="field__control">
            <?= icon('mail', '', 17) ?>
            <input type="email" name="email" value="<?= e(old('email')) ?>" required autocomplete="email" autofocus placeholder="nome@exemplo.pt" class="<?= error('email') ? 'is-invalid' : '' ?>">
        </span>
        <?php if (error('email')): ?><small class="field__error"><?= e(error('email')) ?></small><?php endif; ?>
    </label>

    <label class="field">
        <span><?= e(t('common.password')) ?></span>
        <span class="field__control">
            <?= icon('lock', '', 17) ?>
            <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="<?= error('password') ? 'is-invalid' : '' ?>" data-password-input>
            <button type="button" class="field__reveal" data-password-toggle aria-label="<?= e(t('common.show_password')) ?>"><?= icon('eye', '', 16) ?></button>
        </span>
        <?php if (error('password')): ?><small class="field__error"><?= e(error('password')) ?></small><?php endif; ?>
    </label>

    <div class="auth-form__row">
        <span class="auth-form__secure"><?= icon('shield-check', '', 15) ?> <?= e(t('checkout.payment_simulated')) ?></span>
    </div>

    <button type="submit" class="btn btn--primary btn--lg btn--block"><?= icon('logout', '', 17) ?> <?= e(t('auth.login_cta')) ?></button>
</form>

<div class="auth-switch">
    <p><?= e(t('auth.no_account')) ?> <a href="<?= url('/registo') ?>"><?= e(t('nav.register')) ?></a></p>
    <p><?= e(t('auth.company_register_hint')) ?> <a href="<?= url('/registo?tipo=company') ?>"><?= e(t('auth.register_company')) ?></a></p>
</div>

<div class="demo-accounts">
    <div class="demo-accounts__head">
        <?= icon('info', '', 16) ?>
        <strong><?= e(t('auth.demo_accounts')) ?></strong>
    </div>
    <div class="demo-accounts__grid">
        <button type="button" class="demo-account" data-demo-email="cliente@trivex.pt" data-demo-password="Cliente123!">
            <span class="avatar avatar--sm">JS</span>
            <span><strong><?= e(t('auth.register_user')) ?></strong><small>cliente@trivex.pt</small></span>
        </button>
        <button type="button" class="demo-account" data-demo-email="empresa@trivex.pt" data-demo-password="Empresa123!">
            <span class="avatar avatar--sm avatar--blue">MF</span>
            <span><strong><?= e(t('auth.register_company')) ?></strong><small>empresa@trivex.pt</small></span>
        </button>
        <button type="button" class="demo-account" data-demo-email="moderador@trivex.pt" data-demo-password="Mod12345!">
            <span class="avatar avatar--sm avatar--amber">RN</span>
            <span><strong><?= e(t('admin.role_moderator')) ?></strong><small>moderador@trivex.pt</small></span>
        </button>
        <button type="button" class="demo-account" data-demo-email="admin@trivex.pt" data-demo-password="Admin123!">
            <span class="avatar avatar--sm avatar--dark">AM</span>
            <span><strong><?= e(t('admin.role_admin')) ?></strong><small>admin@trivex.pt</small></span>
        </button>
    </div>
</div>
