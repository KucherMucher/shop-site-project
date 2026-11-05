<?php
use App\Core\Auth;
$status = $company['status'] ?? 'pending';
$rejected = $status === 'rejected';
?>
<div class="auth-card__head">
    <span class="eyebrow"><?= icon('building', '', 13) ?> <?= e(t('nav.company')) ?></span>
    <h1><?= e($rejected ? t('auth.company_rejected_title') : t('auth.company_pending_title')) ?></h1>
    <p><?= e($rejected ? t('auth.company_rejected_text') : t('auth.company_pending_text')) ?></p>
</div>

<div class="company-status-card <?= $rejected ? 'company-status-card--rejected' : '' ?>">
    <span class="company-status-card__icon"><?= icon($rejected ? 'alert' : 'clock', '', 30) ?></span>
    <div>
        <strong><?= e($company['name'] ?? t('nav.company')) ?></strong>
        <?php if ($company): ?>
            <p><?= e(t('company.contact')) ?>: <?= e($company['contact_name']) ?> · <?= e($company['email']) ?></p>
            <span class="status-pill <?= $rejected ? 'status-red' : 'status-amber' ?>"><?= e(t('company.status_' . $status)) ?></span>
            <?php if ($rejected && !empty($company['review_note'])): ?>
                <p class="company-status-card__note"><?= e(t('company.rejected_reason')) ?>: <?= e($company['review_note']) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="verify-steps">
    <div class="verify-step"><b>01</b><span><?= e(t('company.pending_step_1')) ?></span></div>
    <div class="verify-step"><b>02</b><span><?= e(t('company.pending_step_2')) ?></span></div>
    <div class="verify-step"><b>03</b><span><?= e(t('company.pending_step_3')) ?></span></div>
</div>

<div class="auth-actions">
    <a class="btn btn--primary btn--block" href="<?= url('/suporte') ?>"><?= icon('headset', '', 16) ?> <?= e(t('pages.need_help')) ?></a>
    <?php if (Auth::check()): ?>
        <form action="<?= url('/logout') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn--ghost btn--block"><?= icon('logout', '', 16) ?> <?= e(t('nav.logout')) ?></button>
        </form>
    <?php else: ?>
        <a class="btn btn--ghost btn--block" href="<?= url('/login') ?>"><?= icon('user', '', 16) ?> <?= e(t('nav.login')) ?></a>
    <?php endif; ?>
</div>
